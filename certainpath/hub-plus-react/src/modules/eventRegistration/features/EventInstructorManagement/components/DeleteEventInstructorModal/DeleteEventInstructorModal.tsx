import React from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { deleteEventInstructorAction } from "@/modules/eventRegistration/features/EventInstructorManagement/slices/eventInstructorSlice";

interface DeleteEventInstructorModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  instructorId: number | null;
}

const DeleteEventInstructorModal: React.FC<DeleteEventInstructorModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
  instructorId,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification?.() || {};
  const { deleteLoading, deleteError } = useAppSelector(
    (state: RootState) => state.eventInstructor,
  );

  const handleDelete = () => {
    if (!instructorId) return;

    dispatch(
      deleteEventInstructorAction(instructorId, () => {
        showNotification?.(
          "Success",
          "Instructor deleted successfully.",
          "success",
        );
        onSuccess();
        onClose();
      }),
    );
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
      <h3 className="text-xl font-semibold text-gray-900">Delete Instructor</h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to delete this instructor? This action cannot be
        undone.
      </p>

      {deleteError && (
        <p className="mt-2 text-sm text-red-600">{deleteError}</p>
      )}

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={deleteLoading} onClick={onClose} variant="outline">
          Cancel
        </Button>
        <Button
          disabled={deleteLoading || !instructorId}
          onClick={handleDelete}
        >
          {deleteLoading ? "Deleting..." : "Delete Instructor"}
        </Button>
      </div>
    </Modal>
  );
};

export default DeleteEventInstructorModal;
