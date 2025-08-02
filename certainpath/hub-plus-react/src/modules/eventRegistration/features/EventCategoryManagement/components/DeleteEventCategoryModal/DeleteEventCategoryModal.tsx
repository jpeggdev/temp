import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch } from "@/app/hooks";
import { deleteEventCategoryAction } from "@/modules/eventRegistration/features/EventCategoryManagement/slice/eventCategorySlice";
interface DeleteEventCategoryModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  categoryId: number | null;
}

const DeleteEventCategoryModal: React.FC<DeleteEventCategoryModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
  categoryId,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification?.() || {};
  const [isDeleting, setIsDeleting] = useState(false);

  const handleDelete = async () => {
    if (!categoryId) return;
    setIsDeleting(true);

    await dispatch(
      deleteEventCategoryAction(categoryId, () => {
        showNotification?.(
          "Success",
          "Event category deleted successfully.",
          "success",
        );
        onSuccess();
      }),
    );

    setIsDeleting(false);
  };

  return (
    <Modal
      isOpen={isOpen}
      onRequestClose={onClose}
      style={{
        content: {
          top: "50%",
          left: "50%",
          transform: "translate(-50%, -50%)",
          borderRadius: "8px",
          padding: "24px",
          background: "white",
          boxShadow: "0 10px 25px rgba(0,0,0,.3)",
          width: "calc(100% - 32px)",
          maxWidth: "500px",
          minBlockSize: "fit-content",
          overflowY: "auto",
        },
        overlay: {
          backgroundColor: "rgba(0,0,0,0.3)",
          zIndex: 9999,
        },
      }}
    >
      <h3 className="text-xl font-semibold text-gray-900">
        Delete Event Category
      </h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to delete this event category? This action cannot
        be undone.
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={isDeleting} onClick={onClose} variant="outline">
          Cancel
        </Button>
        <Button disabled={isDeleting || !categoryId} onClick={handleDelete}>
          {isDeleting ? "Deleting..." : "Delete Category"}
        </Button>
      </div>
    </Modal>
  );
};

export default DeleteEventCategoryModal;
