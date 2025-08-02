import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import { AppDispatch } from "@/app/store";
import {
  clearCampaignDetails,
  fetchCampaignDetailsAction,
} from "../slices/CampaignDetailsSlice";

export const useViewCampaignDetails = () => {
  const { campaignId } = useParams<{ campaignId: string }>();
  const dispatch = useDispatch<AppDispatch>();

  const { campaignDetails, loading, error } = useSelector(
    (state: RootState) => state.campaignDetails,
  );

  const [showStopModal, setShowStopModal] = useState(false);
  const [showPauseModal, setShowPauseModal] = useState(false);
  const [showResumeModal, setShowResumeModal] = useState(false);

  useEffect(() => {
    if (campaignId) {
      dispatch(fetchCampaignDetailsAction({ campaignId }));
    }

    return () => {
      dispatch(clearCampaignDetails());
    };
  }, [campaignId, dispatch]);

  const handleShowStopModal = () => setShowStopModal(true);
  const handleCloseStopModal = () => setShowStopModal(false);
  const handleShowPauseModal = () => setShowPauseModal(true);
  const handleClosePauseModal = () => setShowPauseModal(false);
  const handleShowResumeModal = () => setShowResumeModal(true);
  const handleCloseResumeModal = () => setShowResumeModal(false);

  const handleStopModalSuccess = () => {
    setShowStopModal(false);
    if (campaignId) {
      dispatch(fetchCampaignDetailsAction({ campaignId }));
    }
  };

  const handlePauseModalSuccess = () => {
    setShowPauseModal(false);
    if (campaignId) {
      dispatch(fetchCampaignDetailsAction({ campaignId }));
    }
  };

  const handleResumeModalSuccess = () => {
    setShowResumeModal(false);
    if (campaignId) {
      dispatch(fetchCampaignDetailsAction({ campaignId }));
    }
  };

  return {
    campaignDetails,
    loading,
    error,
    showStopModal,
    showPauseModal,
    showResumeModal,
    handleShowStopModal,
    handleCloseStopModal,
    handleShowPauseModal,
    handleClosePauseModal,
    handleShowResumeModal,
    handleCloseResumeModal,
    handleStopModalSuccess,
    handlePauseModalSuccess,
    handleResumeModalSuccess,
  };
};
