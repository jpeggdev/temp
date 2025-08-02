import React from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";

interface DuplicateEmailCampaignModalProps {
  isOpen: boolean;
  isDuplicating: boolean;
  onClose: () => void;
  handleDuplicate: () => void;
}

const DuplicateEmailCampaignModal: React.FC<
  DuplicateEmailCampaignModalProps
> = ({ isOpen, onClose, isDuplicating, handleDuplicate }) => {
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
        Duplicate Email Campaign
      </h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to duplicate this email campaign?
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={isDuplicating} onClick={onClose} variant="outline">
          Cancel
        </Button>
        <Button disabled={isDuplicating} onClick={() => handleDuplicate()}>
          {isDuplicating ? "Duplicating..." : "Duplicate Email Campaign"}
        </Button>
      </div>
    </Modal>
  );
};

export default DuplicateEmailCampaignModal;
