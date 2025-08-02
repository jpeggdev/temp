import React from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext"; // if you have it
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { deleteEventAction } from "../../slices/eventListSlice";
import { RootState } from "@/app/rootReducer";

interface DeleteEventModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  eventId: number | null;
}

const DeleteEventModal: React.FC<DeleteEventModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
  eventId,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification?.() || {};

  const deleteLoading = useAppSelector(
    (state: RootState) => state.eventList.deleteLoading,
  );

  const handleDelete = async () => {
    if (!eventId) return;
    await dispatch(
      deleteEventAction(eventId, () => {
        showNotification?.(
          "Event Deleted",
          "Your event has been deleted successfully.",
          "success",
        );
        onSuccess();
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
      <h3 className="text-xl font-semibold text-gray-900">Delete Event</h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to delete this event? This action cannot be
        undone.
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={deleteLoading} onClick={onClose} variant="outline">
          Cancel
        </Button>

        <Button disabled={deleteLoading} onClick={handleDelete}>
          {deleteLoading ? "Deleting..." : "Delete Event"}
        </Button>
      </div>
    </Modal>
  );
};

export default DeleteEventModal;
