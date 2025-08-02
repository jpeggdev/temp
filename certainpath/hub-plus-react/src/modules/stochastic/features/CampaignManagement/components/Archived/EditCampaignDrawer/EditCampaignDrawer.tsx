import React, { useEffect, Fragment, useState } from "react";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  Transition,
} from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import {
  fetchCampaignAction,
  clearCampaignData,
} from "../../../slices/campaignSlice";
import Modal from "react-modal";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import ResumeCampaignModal from "@/modules/stochastic/features/CampaignManagement/components/ResumeCampaignModal/ResumeCampaignModal";
import PauseCampaignModal from "@/modules/stochastic/features/CampaignManagement/components/PauseCampaignModal/PauseCampaignModal";
import StopCampaignModal from "@/modules/stochastic/features/CampaignManagement/components/StopCampaignModal/StopCampaignModal";

interface EditCampaignDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  refetchCampaigns: () => void;
}

Modal.setAppElement("#root");

const EditCampaignDrawer: React.FC<EditCampaignDrawerProps> = ({
  isOpen,
  onClose,
  refetchCampaigns,
}) => {
  const dispatch = useDispatch();
  const { campaign, errorFetch, loadingFetch, selectedCampaignId } =
    useSelector((state: RootState) => state.campaign);
  const [showStopModal, setShowStopModal] = useState(false);
  const [showPauseModal, setShowPauseModal] = useState(false);
  const [showResumeModal, setShowResumeModal] = useState(false);

  useEffect(() => {
    if (isOpen && selectedCampaignId) {
      dispatch(fetchCampaignAction(selectedCampaignId));
    }
  }, [isOpen, dispatch, selectedCampaignId]);

  const handleClose = () => {
    dispatch(clearCampaignData());
    onClose();
  };

  const campaignName = campaign?.name ?? "";
  const productName = campaign?.campaignProduct?.name ?? "";
  const startDate = campaign?.startDate ?? "";
  const endDate = campaign?.endDate ?? "";
  const statusName = campaign?.campaignStatus?.name ?? "";

  const frequency =
    campaign?.mailingIterationWeeks !== undefined &&
    campaign?.mailingIterationWeeks !== null
      ? String(campaign.mailingIterationWeeks)
      : "";

  const description = campaign?.description ?? "";
  const phoneNumber = campaign?.phoneNumber ?? "";

  const canStopOrPause = campaign?.campaignStatus?.id === 1;
  const canResume = campaign?.campaignStatus?.name === "paused";

  const handleCampaignStatusChange = () => {
    refetchCampaigns();
    hideModals();
    onClose();
  };

  const hideModals = () => {
    setShowStopModal(false);
    setShowPauseModal(false);
    setShowResumeModal(false);
  };

  const renderReadOnlyField = (
    label: string,
    value: string,
    multiline = false,
  ) => {
    return (
      <div>
        <label className="block text-sm font-medium text-gray-900">
          {label}
        </label>
        {loadingFetch ? (
          <Skeleton className="mt-2" height={38} />
        ) : multiline ? (
          <textarea
            className="mt-2 block w-full rounded-md border-0 p-2 bg-gray-100 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm"
            disabled
            rows={4}
            value={value}
          />
        ) : (
          <input
            className="mt-2 block w-full rounded-md border-0 p-2 bg-gray-100 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm"
            disabled
            value={value}
          />
        )}
      </div>
    );
  };

  if (!selectedCampaignId) {
    return null;
  }

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog className="relative z-30" onClose={handleClose}>
        <Transition.Child
          as={Fragment}
          enter="transition-opacity ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="transition-opacity ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black bg-opacity-30" />
        </Transition.Child>

        <div className="fixed inset-0 overflow-hidden">
          <div className="absolute inset-0 overflow-hidden">
            <div className="fixed inset-y-0 right-0 flex max-w-full">
              <Transition.Child
                as={Fragment}
                enter="transform transition ease-in-out duration-500"
                enterFrom="translate-x-full"
                enterTo="translate-x-0"
                leave="transform transition ease-in-out duration-500"
                leaveFrom="translate-x-0"
                leaveTo="translate-x-full"
              >
                <DialogPanel className="pointer-events-auto w-screen max-w-md">
                  <div className="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl">
                    {/* HEADER */}
                    <div className="h-0 flex-1 overflow-y-auto">
                      <div className="bg-primary dark:bg-secondary px-4 py-6 sm:px-6">
                        <div className="flex items-center justify-between">
                          <DialogTitle className="text-base font-semibold text-white">
                            Campaign Details
                          </DialogTitle>
                          <button
                            className="relative rounded-md text-white hover:text-white focus:outline-none focus:ring-2 focus:ring-white"
                            onClick={handleClose}
                            type="button"
                          >
                            <XMarkIcon aria-hidden="true" className="h-6 w-6" />
                          </button>
                        </div>
                        <p className="text-sm text-white mt-1">
                          View campaign information below.
                        </p>
                        {errorFetch && (
                          <p className="mt-2 text-sm text-red-500">
                            {errorFetch}
                          </p>
                        )}
                      </div>

                      <div className="flex flex-1 flex-col px-4 sm:px-6 space-y-6 pb-5 pt-6">
                        {renderReadOnlyField("Campaign Name", campaignName)}
                        {renderReadOnlyField("Product", productName)}
                        {renderReadOnlyField("Start Date", startDate)}
                        {renderReadOnlyField("End Date", endDate)}
                        {renderReadOnlyField("Status", statusName)}
                        {renderReadOnlyField(
                          "Mailing Frequency (Weeks)",
                          frequency,
                        )}
                        {renderReadOnlyField("Description", description, true)}
                        {renderReadOnlyField("Phone Number", phoneNumber)}
                      </div>
                    </div>

                    <div className="flex flex-col space-y-3 px-4 py-4">
                      {canStopOrPause && (
                        <div className="flex space-x-4">
                          <button
                            className="inline-flex flex-1 justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-light"
                            disabled={loadingFetch}
                            onClick={() => setShowStopModal(true)}
                            type="button"
                          >
                            Stop Campaign
                          </button>
                          <button
                            className="inline-flex flex-1 justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-light"
                            disabled={loadingFetch}
                            onClick={() => setShowPauseModal(true)}
                            type="button"
                          >
                            Pause Campaign
                          </button>
                        </div>
                      )}

                      {canResume && (
                        <div className="flex space-x-4">
                          <button
                            className="inline-flex flex-1 justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-light"
                            disabled={loadingFetch}
                            onClick={() => setShowResumeModal(true)}
                            type="button"
                          >
                            Resume Campaign
                          </button>
                        </div>
                      )}

                      <button
                        className="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        onClick={handleClose}
                        type="button"
                      >
                        Close
                      </button>
                    </div>
                  </div>
                </DialogPanel>
              </Transition.Child>
            </div>
          </div>
        </div>

        <StopCampaignModal
          campaignId={selectedCampaignId}
          isOpen={showStopModal}
          onClose={() => setShowStopModal(false)}
          onSuccess={handleCampaignStatusChange}
        />

        <PauseCampaignModal
          campaignId={selectedCampaignId}
          isOpen={showPauseModal}
          onClose={() => setShowPauseModal(false)}
          onSuccess={handleCampaignStatusChange}
        />

        <ResumeCampaignModal
          campaignId={selectedCampaignId}
          isOpen={showResumeModal}
          onClose={() => setShowResumeModal(false)}
          onSuccess={handleCampaignStatusChange}
        />
      </Dialog>
    </Transition.Root>
  );
};

export default EditCampaignDrawer;
