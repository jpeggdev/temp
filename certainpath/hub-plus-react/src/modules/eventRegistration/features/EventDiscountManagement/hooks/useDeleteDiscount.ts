import { useCallback, useState } from "react";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch } from "@/app/hooks";
import { deleteDiscountAction } from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountSlice";
import { useNavigate } from "react-router-dom";

export function useDeleteDiscount() {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const navigate = useNavigate();
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleDelete = async () => {
    if (!deleteId) return;
    setIsDeleting(true);

    try {
      await dispatch(deleteDiscountAction(deleteId));
      showNotification(
        "Discount Deleted",
        "The discount was deleted successfully.",
        "success",
      );
      setDeleteId(null);
      setShowDeleteModal(false);
      navigate("/event-registration/admin/discounts");
    } catch (error) {
      console.error("Failed to delete Discount:", error);
      showNotification("Error", "Failed to delete the discount.", "error");
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
