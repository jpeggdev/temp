import React, { useState } from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { pauseCampaign } from "@/api/pauseCampaign/pauseCampaignApi";
import { useNotification } from "@/context/NotificationContext";

interface PauseCampaignModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  campaignId: number;
}

const PauseCampaignModal: React.FC<PauseCampaignModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
  campaignId,
}) => {
  const { showNotification } = useNotification();
  const [isPausing, setIsPausing] = useState(false);

  const handlePauseCampaignClick = async () => {
    setIsPausing(true);
    try {
      await pauseCampaign({ campaignId });

      showNotification(
        "Campaign Paused",
        "Your campaign has been paused successfully.",
        "success",
      );
      onSuccess();
    } catch (error) {
      onClose();
      console.error("Failed to pause campaign:", error);
    } finally {
      setIsPausing(false);
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
      <h3 className="text-xl font-semibold text-gray-900">Pause Campaign</h3>
      <p className="mt-3 text-sm text-gray-600">
        Are you sure you want to pause this campaign?
      </p>

      <div className="mt-6 flex justify-end space-x-3">
        <Button onClick={onClose} variant="plain">
          Cancel
        </Button>

        <Button
          disabled={isPausing}
          onClick={handlePauseCampaignClick}
          variant="default"
        >
          {isPausing ? "Pausing..." : "Pause Campaign"}
        </Button>
      </div>
    </Modal>
  );
};

export default PauseCampaignModal;
