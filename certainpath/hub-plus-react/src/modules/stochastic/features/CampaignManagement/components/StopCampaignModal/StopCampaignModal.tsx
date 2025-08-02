import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext";
import { stopCampaign } from "@/api/stopCampaign/stopCampaignApi";

interface StopCampaignModalProps {
  isOpen: boolean;
  onClose: () => void;
  campaignId: number;
  onSuccess?: () => void;
}

const StopCampaignModal: React.FC<StopCampaignModalProps> = ({
  isOpen,
  onClose,
  campaignId,
  onSuccess,
}) => {
  const { showNotification } = useNotification();
  const [isStopping, setIsStopping] = useState(false);

  const handleStopCampaignClick = async () => {
    setIsStopping(true);
    try {
      await stopCampaign({ campaignId });
      showNotification(
        "Campaign Stopped",
        "Your campaign has been archived successfully.",
        "success",
      );
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      onClose();
      console.error("Failed to stop campaign:", error);
    } finally {
      setIsStopping(false);
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
      <h3 className="text-xl font-semibold text-gray-900">Stop Campaign</h3>
      <p className="mt-3 text-sm text-gray-600">
        Once a campaign is stopped, it cannot be restarted.
        <br />
        Are you sure you want to stop this campaign?
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button onClick={onClose} variant="plain">
          Cancel
        </Button>

        <Button
          disabled={isStopping}
          onClick={handleStopCampaignClick}
          variant="default"
        >
          {isStopping ? "Stopping..." : "Stop Campaign"}
        </Button>
      </div>
    </Modal>
  );
};

export default StopCampaignModal;
