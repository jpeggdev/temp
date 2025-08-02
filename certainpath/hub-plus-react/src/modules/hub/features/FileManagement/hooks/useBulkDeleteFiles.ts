import { useState, useEffect, useRef } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { useSubscription } from "@apollo/client";
import { deleteMultipleNodes } from "../api/deleteMultipleNodes/deleteMultipleNodesApi";
import { ON_FILE_DELETE_JOB_SUBSCRIPTION } from "../graphql/subscriptions/onFileDeleteJob/onFileDeleteJobSubscription";
import {
  FileDeleteJob,
  FileDeleteJobSubscriptionData,
} from "../graphql/subscriptions/onFileDeleteJob/types";
import { clearSelectedItems as clearSelectedItemsAction } from "../slices/fileManagementSlice";
import {
  updateMultipleTagCounts,
  updateMultipleFileTypeCounts,
} from "../slices/fileManagerMetadataSlice";
import { RootState } from "@/app/rootReducer";

interface UseBulkDeleteFilesProps {
  refreshFolder: () => void;
}

export function useBulkDeleteFiles({ refreshFolder }: UseBulkDeleteFilesProps) {
  const dispatch = useAppDispatch();
  const folderItems = useAppSelector(
    (state: RootState) => state.fileManagement.folderItems,
  );

  // Add a ref to track if we've already processed the completion
  const hasRefreshedRef = useRef(false);

  // Store metadata for files to be deleted
  const [pendingTagIds, setPendingTagIds] = useState<number[]>([]);
  const [pendingFileTypes, setPendingFileTypes] = useState<string[]>([]);

  // Dialog state
  const [isBulkDeleteDialogOpen, setIsBulkDeleteDialogOpen] = useState(false);

  // Job tracking state
  const [deleteJobId, setDeleteJobId] = useState<string | null>(null);
  const [deleteJob, setDeleteJob] = useState<FileDeleteJob | null>(null);

  // Loading state
  const [isBulkDeleting, setIsBulkDeleting] = useState(false);

  // Subscribe to file delete job updates
  const { data } = useSubscription<FileDeleteJobSubscriptionData>(
    ON_FILE_DELETE_JOB_SUBSCRIPTION,
    {
      variables: { uuid: deleteJobId || "" },
      skip: !deleteJobId,
    },
  );

  // Update local state when subscription data arrives
  useEffect(() => {
    if (data?.file_delete_job && data.file_delete_job.length > 0) {
      setDeleteJob(data.file_delete_job[0]);

      // Auto-refresh data when complete, but only once
      if (
        data.file_delete_job[0].status === "completed" &&
        !hasRefreshedRef.current
      ) {
        hasRefreshedRef.current = true; // Mark as refreshed

        // Update metadata counts
        if (pendingTagIds.length > 0) {
          dispatch(
            updateMultipleTagCounts({
              tagIds: pendingTagIds,
              increment: false,
            }),
          );
        }

        if (pendingFileTypes.length > 0) {
          dispatch(
            updateMultipleFileTypeCounts({
              fileTypes: pendingFileTypes,
              increment: false,
            }),
          );
        }

        // Refresh folder contents
        refreshFolder();
      }
    }
  }, [data, refreshFolder, dispatch, pendingTagIds, pendingFileTypes]);

  // Reset job tracking when dialog closes
  useEffect(() => {
    if (!isBulkDeleteDialogOpen) {
      // Small delay to allow animation to complete
      setTimeout(() => {
        setDeleteJobId(null);
        setDeleteJob(null);
        setIsBulkDeleting(false);
        setPendingTagIds([]);
        setPendingFileTypes([]);
        // Reset the refresh flag when dialog closes
        hasRefreshedRef.current = false;
      }, 300);
    }
  }, [isBulkDeleteDialogOpen]);

  // Open bulk delete dialog
  const handleOpenBulkDeleteDialog = () => {
    setIsBulkDeleteDialogOpen(true);
  };

  // Close bulk delete dialog
  const handleCloseBulkDeleteDialog = () => {
    setIsBulkDeleteDialogOpen(false);
  };

  // Helper to collect metadata from items to be deleted
  const collectMetadata = (selectedItems: string[]) => {
    const tagIds: number[] = [];
    const fileTypes: string[] = [];

    selectedItems.forEach((uuid) => {
      const item = folderItems.find((item) => item.uuid === uuid);

      if (item) {
        // Collect tags
        if (item.tags && item.tags.length > 0) {
          item.tags.forEach((tag) => {
            tagIds.push(tag.id);
          });
        }

        // Collect file types (only for files)
        if (item.fileType) {
          fileTypes.push(item.fileType);
        }
      }
    });

    return { tagIds, fileTypes };
  };

  // Perform bulk delete operation
  const handleBulkDelete = async (selectedItems: string[]): Promise<void> => {
    if (selectedItems.length === 0) return;

    try {
      setIsBulkDeleting(true);
      // Reset the refresh flag when starting a new deletion
      hasRefreshedRef.current = false;

      // Collect metadata for items to be deleted
      const { tagIds, fileTypes } = collectMetadata(selectedItems);
      setPendingTagIds(tagIds);
      setPendingFileTypes(fileTypes);

      // Make the API call to queue the deletion
      const response = await deleteMultipleNodes({ uuids: selectedItems });

      // Start tracking the job
      if (response.data.jobId) {
        // Create initial job state for immediate feedback
        setDeleteJob({
          id: 0,
          uuid: response.data.jobId,
          status: "pending",
          progress_percent: "0",
          total_files: selectedItems.length,
          processed_files: 0,
          successful_deletes: 0,
          failed_items: null,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        });

        // Set the job ID to trigger the subscription
        setDeleteJobId(response.data.jobId);
      }

      // Clear selection now that the job is queued
      dispatch(clearSelectedItemsAction());
    } catch (error) {
      console.error("Error starting bulk delete:", error);
      setIsBulkDeleting(false);
      setPendingTagIds([]);
      setPendingFileTypes([]);
    }
  };

  return {
    // State
    isBulkDeleteDialogOpen,
    deleteJob,
    isBulkDeleting,

    // Actions
    handleOpenBulkDeleteDialog,
    handleCloseBulkDeleteDialog,
    handleBulkDelete,
  };
}
