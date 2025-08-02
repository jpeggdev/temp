import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useAppDispatch } from "@/app/hooks";
import { deleteEventSessionAction } from "../../slices/eventSessionListSlice";

interface DeleteEventSessionModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  sessionUuid: string | null;
}

function DeleteEventSessionModal({
  isOpen,
  onClose,
  onSuccess,
  sessionUuid,
}: DeleteEventSessionModalProps) {
  const dispatch = useAppDispatch();
  const [isDeleting, setIsDeleting] = useState(false);

  const handleDelete = async () => {
    if (!sessionUuid) return;
    setIsDeleting(true);
    try {
      await dispatch(deleteEventSessionAction(sessionUuid));
      onSuccess();
    } catch (error) {
      console.error("Failed to delete session:", error);
    } finally {
      setIsDeleting(false);
    }
  };

  return (
    <Modal
      ariaHideApp={false}
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
      <h3 className="text-xl font-semibold text-gray-900">Delete Session</h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to delete this session? This action cannot be
        undone.
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={isDeleting} onClick={onClose} variant="outline">
          Cancel
        </Button>
        <Button disabled={isDeleting || !sessionUuid} onClick={handleDelete}>
          {isDeleting ? "Deleting..." : "Delete Session"}
        </Button>
      </div>
    </Modal>
  );
}

export default DeleteEventSessionModal;
