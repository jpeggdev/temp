import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext"; // if you have it
import { useAppDispatch } from "@/app/hooks";
import { deleteResourceAction } from "../../slices/resourceListSlice";

interface DeleteResourceModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  resourceUuid: string | null;
}

const DeleteResourceModal: React.FC<DeleteResourceModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
  resourceUuid,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification?.() || {};
  const [isDeleting, setIsDeleting] = useState(false);

  const handleDelete = async () => {
    if (!resourceUuid) return;
    setIsDeleting(true);
    try {
      await dispatch(deleteResourceAction(resourceUuid));
      showNotification?.(
        "Resource Deleted",
        "Your resource has been deleted successfully.",
        "success",
      );
      onSuccess();
    } catch (error) {
      console.error("Failed to delete resource:", error);
    } finally {
      setIsDeleting(false);
    }
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
      <h3 className="text-xl font-semibold text-gray-900">Delete Resource</h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to delete this resource? This action cannot be
        undone.
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={isDeleting} onClick={onClose} variant="outline">
          Cancel
        </Button>

        <Button disabled={isDeleting || !resourceUuid} onClick={handleDelete}>
          {isDeleting ? "Deleting..." : "Delete Resource"}
        </Button>
      </div>
    </Modal>
  );
};

export default DeleteResourceModal;
