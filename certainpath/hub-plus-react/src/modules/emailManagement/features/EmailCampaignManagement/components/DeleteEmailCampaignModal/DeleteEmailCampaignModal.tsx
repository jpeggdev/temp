import React from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";

interface DeleteEmailCampaignModalProps {
  isOpen: boolean;
  isDeleting: boolean;
  onClose: () => void;
  handleDelete: () => void;
}

const DeleteEmailCampaignModal: React.FC<DeleteEmailCampaignModalProps> = ({
  isOpen,
  onClose,
  isDeleting,
  handleDelete,
}) => {
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
        Delete Email Campaign
      </h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to delete this email campaign? This action cannot
        be undone.
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button disabled={isDeleting} onClick={onClose} variant="outline">
          Cancel
        </Button>
        <Button disabled={isDeleting} onClick={() => handleDelete()}>
          {isDeleting ? "Deleting..." : "Delete Email Campaign"}
        </Button>
      </div>
    </Modal>
  );
};

export default DeleteEmailCampaignModal;
