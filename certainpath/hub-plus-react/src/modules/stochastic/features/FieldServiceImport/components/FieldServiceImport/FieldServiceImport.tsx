import React, { useEffect, useState } from "react";
import { FileUploader } from "react-drag-drop-files";
import { AxiosError, AxiosResponse } from "axios";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { uploadFieldServiceSoftware } from "@/api/uploadFieldServiceSoftware/uploadFieldServiceSoftwareApi";
import Gleap from "gleap";
import { HelpCircle } from "lucide-react";
import { useNotification } from "@/context/NotificationContext";
import { useSubscription } from "@apollo/client";
import { useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import ConfirmationDialog from "@/components/ConfirmationDialog/ConfirmationDialog";
import { ON_MEMBERS_STREAM_COUNT_SUBSCRIPTION } from "@/modules/stochastic/features/FieldServiceImport/graphql/subscriptions/onMembersStreamCount";
import { MembersStreamCountSubscriptionData } from "@/modules/stochastic/features/FieldServiceImport/graphql/subscriptions/onMembersStreamCount/types";
import { extractErrorMessage } from "@/utils/extractErrorMessage";

const fileTypes = ["CSV", "XLSX", "XLS"];
const softwarePackages = [{ value: "ServiceTitan", label: "ServiceTitan" }];
const importTypes = [
  {
    value: "activeCustomers",
    label: "Active Customers (Include ALL customers)",
    description: null,
  },
  {
    value: "invoices",
    label: "Invoices (Include invoices since last upload)",
    description: null,
  },
];

interface TradeOption {
  label: string;
  value: string;
}

interface FieldServiceImportProps {
  supportedTrades?: TradeOption[];
  onUploadComplete?: (jobId: string) => void;
}

export const FieldServiceImport: React.FC<FieldServiceImportProps> = ({
  supportedTrades = [
    { label: "HVAC", value: "hvac" },
    { label: "Electrical", value: "electrical" },
    { label: "Plumbing", value: "plumbing" },
    { label: "Roofing", value: "roofing" },
  ],
}) => {
  const { showNotification } = useNotification();
  const userAppSettings = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );
  const tenantId = userAppSettings?.intacctId;

  const { data: memberStreamData, error: memberStreamError } =
    useSubscription<MembersStreamCountSubscriptionData>(
      ON_MEMBERS_STREAM_COUNT_SUBSCRIPTION,
      {
        variables: { tenantId: tenantId ?? "" },
        skip: !tenantId,
      },
    );

  useEffect(() => {
    if (memberStreamData) {
      console.log(
        "Subscription data for members_stream count:",
        memberStreamData,
      );
    }
  }, [memberStreamData]);

  useEffect(() => {
    if (memberStreamError) {
      console.error(
        "Members stream count subscription error:",
        memberStreamError.message,
      );
    }
  }, [memberStreamError]);

  const [selectedSoftware, setSelectedSoftware] = useState("");
  const [selectedTrade, setSelectedTrade] = useState("");
  const [selectedImportType, setSelectedImportType] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [uploadSuccess, setUploadSuccess] = useState(false);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [, setSelectedFile] = useState<File | null>(null);
  const [showConfirmationModal, setShowConfirmationModal] = useState(false);

  const [dialogTitleContent, setDialogTitleContent] =
    useState<React.ReactNode>("");
  const [dialogInstructionTitle, setDialogInstructionTitle] =
    useState<string>("");
  const [dialogInstructionItems, setDialogInstructionItems] = useState<
    string[]
  >([]);
  const [dialogInstructionFinalQuestion, setDialogInstructionFinalQuestion] =
    useState<string>("");
  const [cancelMessage, setCancelMessage] = useState<string>("");
  const [confirmMessage, setConfirmMessage] = useState<string>("");
  const [cancelHandleFunction, setCancelHandleFunction] = useState<() => void>(
    () => () => {},
  );
  const [confirmHandleFunction, setConfirmHandleFunction] = useState<
    () => void
  >(() => () => {});

  const handleUploadButton = () => {
    if (!file || !selectedSoftware || !selectedImportType || !selectedTrade) {
      const message = "Please fill in all required fields";
      alert(message);
      showNotification("Missing Fields", message, "error");
      return;
    }
    if (selectedImportType === "activeCustomers") {
      setDialogTitleContent("Upload Active Customers");
      setDialogInstructionTitle(
        "You are about to upload a new customer list. This will:",
      );
      setDialogInstructionItems([
        "Mark all existing customers as inactive",
        "Set only the customers in your new list as active",
        "Preserve all historical invoice data",
      ]);
      setDialogInstructionFinalQuestion("Are you sure you want to proceed?");
      setConfirmMessage("Confirm Upload");
      setCancelMessage("Cancel");
      setCancelHandleFunction(() => handleUploadCancelModal);
      setConfirmHandleFunction(() => handleUploadConfirmModal);
      setShowConfirmationModal(true);
    } else {
      doActualUpload();
    }
  };

  const doActualUpload = async () => {
    if (!file) return;
    setUploadError(null);
    setUploadSuccess(false);
    setSelectedFile(file);
    setIsUploading(true);
    setUploadProgress(0);

    try {
      await uploadFieldServiceSoftware(
        {
          file,
          software: selectedSoftware,
          trade: selectedTrade,
          importType: selectedImportType,
        },
        (progressEvent) => {
          const percentCompleted = Math.round(
            (progressEvent.loaded * 100) / (progressEvent.total || 1),
          );
          setUploadProgress(percentCompleted);
        },
      );

      setUploadSuccess(true);
      setFile(null);
      setSelectedSoftware("");
      setSelectedTrade("");
      setSelectedImportType("");
      setSelectedFile(null);
      showNotification("Success", `File successfully uploaded!`, "success");
    } catch (err) {
      let errorMessage = "An error occurred.";
      try {
        const typedError = err as AxiosError;
        if (typedError.response) {
          errorMessage = await extractErrorMessage(
            typedError.response as AxiosResponse,
          );
        }
      } catch (parseErr) {
        console.error("Error parsing upload error:", parseErr);
      }
      setUploadError(errorMessage);
      setDialogTitleContent("Upload Error");
      setDialogInstructionTitle(errorMessage);
      setDialogInstructionItems([]);
      setDialogInstructionFinalQuestion("");
      setCancelMessage("Close");
      setConfirmMessage("");
      setCancelHandleFunction(() => handleUploadCancelModal);
      setConfirmHandleFunction(() => () => {});
      setShowConfirmationModal(true);
    } finally {
      setIsUploading(false);
    }
  };

  useEffect(() => {
    if (isUploading || uploadSuccess || uploadError) {
      document
        .getElementById("upload-status")
        ?.scrollIntoView({ behavior: "smooth" });
    }
  }, [isUploading, uploadSuccess, uploadError]);

  const handleUploadConfirmModal = async () => {
    setShowConfirmationModal(false);
    await doActualUpload();
  };

  const handleUploadCancelModal = () => {
    setShowConfirmationModal(false);
  };

  return (
    <MainPageWrapper
      title="Field Service Import"
      titleHelpIcon={
        <HelpCircle
          className="w-5 h-5 text-gray-500 hover:text-gray-700 cursor-pointer"
          onClick={() => Gleap.openHelpCenterCollection("3", false)}
        />
      }
    >
      <div>
        <div className="mb-4">
          <label className="block mb-1 text-sm font-medium">
            Software Package
          </label>
          <select
            className="p-2 border rounded w-72"
            onChange={(e) => setSelectedSoftware(e.target.value)}
            value={selectedSoftware}
          >
            <option value="">Select software package</option>
            {softwarePackages.map((software) => (
              <option key={software.value} value={software.value}>
                {software.label}
              </option>
            ))}
          </select>
        </div>

        {supportedTrades.length > 1 && (
          <div className="mb-4">
            <label className="block mb-1 text-sm font-medium">Trade</label>
            <select
              className="p-2 border rounded w-72"
              onChange={(e) => setSelectedTrade(e.target.value)}
              value={selectedTrade}
            >
              <option value="">Select trade</option>
              {supportedTrades.map((trade) => (
                <option key={trade.value} value={trade.value}>
                  {trade.label}
                </option>
              ))}
            </select>
          </div>
        )}

        <div className="mb-6">
          <label className="block mb-1 text-sm font-medium">Import Type</label>
          {importTypes.map((type) => (
            <div className="mb-2" key={type.value}>
              <label className="flex items-start">
                <input
                  checked={selectedImportType === type.value}
                  className="mt-1 mr-2"
                  name="importType"
                  onChange={(e) => setSelectedImportType(e.target.value)}
                  type="radio"
                  value={type.value}
                />
                <div>
                  <div>{type.label}</div>
                  {type.description && (
                    <div className="text-sm text-gray-500">
                      {type.description}
                    </div>
                  )}
                </div>
              </label>
            </div>
          ))}
        </div>

        <div className="mb-4">
          <label className="block mb-1 text-sm font-medium">File</label>
          <FileUploader
            handleChange={(f: File) => setFile(f)}
            maxSize={100}
            name="file"
            types={fileTypes}
            uploadedLabel="Uploaded Successfully!"
          />
          {file && (
            <p className="mt-2 text-sm text-gray-600">
              Selected file: {file.name}
            </p>
          )}
        </div>

        <button
          className="mt-2 p-2 bg-blue-500 text-white rounded disabled:bg-gray-300"
          disabled={!file || isUploading}
          onClick={handleUploadButton}
        >
          {isUploading ? "Uploading and Processing: Please wait ..." : "Upload"}
        </button>

        <div className="mb-4" id="upload-status">
          {isUploading && (
            <div className="mt-4">
              <div className="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  className="bg-blue-500 h-2.5 rounded-full"
                  style={{ width: `${uploadProgress}%` }}
                />
              </div>
              <div className="mt-2 text-sm text-gray-500">
                {uploadProgress}%
              </div>
            </div>
          )}
        </div>
      </div>

      <ConfirmationDialog
        cancelMessage={cancelMessage}
        confirmMessage={confirmMessage}
        dialogInstructionFinalQuestion={dialogInstructionFinalQuestion}
        dialogInstructionItems={dialogInstructionItems}
        dialogInstructionTitle={dialogInstructionTitle}
        dialogTitleContent={dialogTitleContent}
        isOpen={showConfirmationModal}
        onClose={cancelHandleFunction}
        onConfirm={confirmHandleFunction}
      />
    </MainPageWrapper>
  );
};
