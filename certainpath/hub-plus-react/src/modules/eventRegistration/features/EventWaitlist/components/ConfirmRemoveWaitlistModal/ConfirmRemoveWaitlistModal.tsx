import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/ui/button";

interface ConfirmRemoveWaitlistModalProps {
  isOpen: boolean;
  onClose: () => void;
  userFullName: string;
  onConfirm: () => Promise<void>;
}

function ConfirmRemoveWaitlistModal({
  isOpen,
  onClose,
  userFullName,
  onConfirm,
}: ConfirmRemoveWaitlistModalProps) {
  const [isWorking, setIsWorking] = useState(false);

  const handleConfirm = async () => {
    setIsWorking(true);
    try {
      await onConfirm();
      onClose();
    } catch (error) {
      console.error("Failed to remove from waitlist:", error);
    } finally {
      setIsWorking(false);
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
      <h3 className="text-xl font-semibold text-gray-900">
        Remove from Waitlist
      </h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to remove <strong>{userFullName}</strong> from the
        waitlist?
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={isWorking} onClick={onClose} variant="outline">
          Cancel
        </Button>
        <Button disabled={isWorking} onClick={handleConfirm}>
          {isWorking ? "Removing..." : "Remove"}
        </Button>
      </div>
    </Modal>
  );
}

export default ConfirmRemoveWaitlistModal;
