import React from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { duplicateEventAction } from "../../slices/eventListSlice";

interface DuplicateEventModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  eventId: number | null;
}

function DuplicateEventModal({
  isOpen,
  onClose,
  onSuccess,
  eventId,
}: DuplicateEventModalProps) {
  const dispatch = useAppDispatch();

  const duplicateLoading = useAppSelector(
    (state: RootState) => state.eventList.duplicateLoading,
  );

  const handleDuplicate = async () => {
    if (!eventId) return;
    dispatch(
      duplicateEventAction(eventId, () => {
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
      <h3 className="text-xl font-semibold text-gray-900">Duplicate Event</h3>
      <p className="mt-3 text-sm text-gray-600">
        Duplicating this event will create a new event with the same details and
        files. A new event code will be generated automatically, and the copied
        event will be unpublished by default. Are you sure you want to proceed?
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={duplicateLoading} onClick={onClose} variant="outline">
          Cancel
        </Button>

        <Button
          disabled={duplicateLoading || eventId == null}
          onClick={handleDuplicate}
        >
          {duplicateLoading ? "Duplicating..." : "Duplicate Event"}
        </Button>
      </div>
    </Modal>
  );
}

export default DuplicateEventModal;
