import { useCallback, useState } from "react";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch } from "@/app/hooks";
import { duplicateEmailCampaignAction } from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignSlice";

interface DeleteEmailCampaignProps {
  refetchEmailCampaigns: () => void;
}

export function useDuplicateEmailCampaign({
  refetchEmailCampaigns,
}: DeleteEmailCampaignProps) {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const [duplicateId, setDuplicateId] = useState<number | null>(null);
  const [isDuplicating, setIsDuplicating] = useState(false);
  const [showDuplicateModal, setShowDuplicateModal] = useState(false);

  const handleDuplicate = async () => {
    if (!duplicateId) return;
    setIsDuplicating(true);

    try {
      await dispatch(duplicateEmailCampaignAction(duplicateId));
      showNotification(
        "Email Campaign Duplicated",
        "The email campaign was duplicated successfully.",
        "success",
      );
      setDuplicateId(null);
      setShowDuplicateModal(false);
      refetchEmailCampaigns();
    } catch (error) {
      console.error("Failed to duplicate Email Campaign:", error);
      showNotification(
        "Error",
        "Failed to duplicate the Email Campaign.",
        "error",
      );
    } finally {
      setIsDuplicating(false);
    }
  };

  const handleShowDuplicateModal = useCallback((id: number) => {
    setDuplicateId(id);
    setShowDuplicateModal(true);
  }, []);

  const handleCloseDuplicateModal = useCallback(() => {
    setDuplicateId(null);
    setShowDuplicateModal(false);
  }, []);

  return {
    isDuplicating,
    handleDuplicate,
    showDuplicateModal,
    handleShowDuplicateModal,
    handleCloseDuplicateModal,
  };
}
