import { useCallback, useState } from "react";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch } from "@/app/hooks";
import { deleteVenueAction } from "@/modules/eventRegistration/features/EventVenueManagement/slices/VenueSlice";

interface DeleteVenueModalProps {
  refetchVenues: () => void;
}

export function useDeleteVenue({ refetchVenues }: DeleteVenueModalProps) {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleDelete = async () => {
    if (!deleteId) return;
    setIsDeleting(true);

    try {
      await dispatch(deleteVenueAction(deleteId));
      showNotification(
        "Venue Deleted",
        "The venue was deleted successfully.",
        "success",
      );
      setDeleteId(null);
      setShowDeleteModal(false);
      refetchVenues();
    } catch (error) {
      console.error("Failed to delete Venue:", error);
      showNotification("Error", "Failed to delete the venue.", "error");
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
