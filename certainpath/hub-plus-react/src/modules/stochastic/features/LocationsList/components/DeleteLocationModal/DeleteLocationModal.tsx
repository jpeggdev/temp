import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch } from "@/app/hooks";
import { deleteLocationAction } from "@/modules/stochastic/features/LocationsList/slices/locationSlice";

interface DeleteLocationModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  onError: () => void;
  locationId: number | null;
}

const DeleteLocationModal: React.FC<DeleteLocationModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
  onError,
  locationId,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification?.() || {};
  const [isDeleting, setIsDeleting] = useState(false);

  const handleDelete = async () => {
    if (!locationId) return;
    setIsDeleting(true);

    try {
      await dispatch(deleteLocationAction(locationId));
      showNotification?.(
        "Location Deleted",
        "The location was deleted successfully.",
        "success",
      );
      onSuccess();
    } catch (error) {
      console.error("Failed to delete Location:", error);
      showNotification?.("Error", "Failed to delete location.", "error");
      onError();
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
          maxHeight: "200px",
          overflowY: "auto",
        },
        overlay: {
          backgroundColor: "rgba(0,0,0,0.3)",
          zIndex: 9999,
        },
      }}
    >
      <h3 className="text-xl font-semibold text-gray-900">Delete Location</h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to delete this location? This action cannot be
        undone.
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={isDeleting} onClick={onClose} variant="default">
          Cancel
        </Button>
        <Button
          disabled={isDeleting || !locationId}
          onClick={handleDelete}
          variant="default"
        >
          {isDeleting ? "Deleting..." : "Delete Location"}
        </Button>
      </div>
    </Modal>
  );
};

export default DeleteLocationModal;
