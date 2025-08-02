import { useCallback, useState } from "react";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch } from "@/app/hooks";
import { deleteEmailCampaignAction } from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignSlice";

interface DeleteEmailCampaignProps {
  refetchEmailCampaigns: () => void;
}

export function useDeleteEmailCampaign({
  refetchEmailCampaigns,
}: DeleteEmailCampaignProps) {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleDelete = async () => {
    if (!deleteId) return;
    setIsDeleting(true);

    try {
      await dispatch(deleteEmailCampaignAction(deleteId));
      showNotification(
        "Email Campaign Deleted",
        "The email campaign was deleted successfully.",
        "success",
      );
      setDeleteId(null);
      setShowDeleteModal(false);
      refetchEmailCampaigns();
    } catch (error) {
      console.error("Failed to delete Email Campaign:", error);
      showNotification(
        "Error",
        "Failed to delete the Email Campaign.",
        "error",
      );
    } finally {
      setIsDeleting(false);
    }
  };

  const handleShowDeleteModal = useCallback((id: number) => {
    setDeleteId(id);
    setShowDeleteModal(true);
  }, []);

  const handleCloseDeleteModal = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
  }, []);

  return {
    isDeleting,
    handleDelete,
    showDeleteModal,
    handleShowDeleteModal,
    handleCloseDeleteModal,
  };
}
