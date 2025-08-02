import React from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";

interface ConfirmAddToDoNotMailListModalProps {
  isOpen: boolean;
  isAdding: boolean;
  matchesCount: number;
  onClose: () => void;
  handleConfirm: () => void;
}

const ConfirmAddToDoNotMailListModal: React.FC<
  ConfirmAddToDoNotMailListModalProps
> = ({ isOpen, isAdding, onClose, handleConfirm, matchesCount }) => {
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
        Add to Do Not Mail List
      </h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to add {matchesCount} new{" "}
        {matchesCount === 1 ? "address" : "addresses"} to the do not mail list?
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={false} onClick={onClose} variant="outline">
          Cancel
        </Button>
        <Button disabled={isAdding} onClick={handleConfirm}>
          {isAdding ? "Processing..." : "Confirm"}
        </Button>
      </div>
    </Modal>
  );
};

export default ConfirmAddToDoNotMailListModal;
