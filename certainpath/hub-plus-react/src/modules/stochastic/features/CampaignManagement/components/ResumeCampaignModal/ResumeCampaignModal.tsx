import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext";
import { resumeCampaign } from "@/api/resumeCampaign/resumeCampaignApi";

interface ResumeCampaignModalProps {
  isOpen: boolean;
  onClose: () => void;
  campaignId: number;
  onSuccess?: () => void;
}

const ResumeCampaignModal: React.FC<ResumeCampaignModalProps> = ({
  isOpen,
  onClose,
  campaignId,
  onSuccess,
}) => {
  const { showNotification } = useNotification();
  const [isResuming, setIsResuming] = useState(false);

  const handleResumeCampaignClick = async () => {
    setIsResuming(true);
    try {
      await resumeCampaign({ campaignId });
      showNotification(
        "Campaign Resumed",
        "Your campaign has been queued for resuming.",
        "success",
      );
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      onClose();
      console.error("Failed to resume campaign:", error);
    } finally {
      setIsResuming(false);
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
          right: "auto",
          bottom: "auto",
          transform: "translate(-50%, -50%)",
          borderRadius: "8px",
          padding: "24px",
          width: "500px",
          background: "white",
          boxShadow: "0 10px 25px rgba(0,0,0,.3)",
        },
        overlay: {
          backgroundColor: "rgba(0,0,0,0.3)",
          zIndex: 9999,
        },
      }}
    >
      <h3 className="text-xl font-semibold text-gray-900">Resume Campaign</h3>
      <p className="mt-3 text-sm text-gray-600">
        Resuming the campaign will pick up where it left off.
      </p>
      <p className="mt-3 text-sm text-gray-600">
        Note: The campaign end date will automatically be extended by the number
        of weeks the campaign was paused.
      </p>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to resume this campaign?
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button onClick={onClose} variant="plain">
          Cancel
        </Button>
        <Button
          disabled={isResuming}
          onClick={handleResumeCampaignClick}
          variant="default"
        >
          {isResuming ? "Resuming..." : "Resume Campaign"}
        </Button>
      </div>
    </Modal>
  );
};

export default ResumeCampaignModal;
