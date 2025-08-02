import { useCallback, useState } from "react";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch } from "@/app/hooks";
import { deleteVoucherAction } from "@/modules/eventRegistration/features/EventVoucherManagement/slices/VoucherSlice";

interface DeleteVoucherModalProps {
  refetchVouchers: () => void;
}

export function useDeleteVoucher({ refetchVouchers }: DeleteVoucherModalProps) {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleDelete = async () => {
    if (!deleteId) return;
    setIsDeleting(true);

    try {
      await dispatch(deleteVoucherAction(deleteId));
      showNotification(
        "Voucher Deleted",
        "The voucher was deleted successfully.",
        "success",
      );
      setDeleteId(null);
      setShowDeleteModal(false);
      refetchVouchers();
    } catch (error) {
      console.error("Failed to delete Voucher:", error);
      showNotification("Error", "Failed to delete the voucher.", "error");
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
