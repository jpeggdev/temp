import React, { useEffect, useState } from "react";
import { FileUploader } from "react-drag-drop-files";
import { toast } from "react-toastify";
import { AxiosError, AxiosResponse } from "axios";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ConfirmationDialog from "@/components/ConfirmationDialog/ConfirmationDialog";
import { uploadPostageExpense } from "@/api/uploadPostageExpense/uploadPostageExpenseApi";
import { extractErrorMessage } from "@/utils/extractErrorMessage";
import { useNotification } from "@/context/NotificationContext";
import { Button } from "@/components/ui/button";

const FILE_TYPES = ["CSV", "XLSX", "XLS"];

const POSTAL_VENDORS = [
  {
    value: "usps",
    label: "US Postal Service",
  },
];

const IMPORT_TYPES = [
  {
    value: "expense",
    label: "Expense",
    description: "USPS Transactions and Billing.",
  },
];

interface PostageExpenseImportProps {
  onUploadComplete?: (jobId: string) => void;
}

interface DialogState {
  titleContent: React.ReactNode;
  instructionTitle: string;
  instructionItems: string[];
  instructionFinalQuestion: string;
  cancelMessage: string;
  confirmMessage: string;
  cancelHandler: () => void;
  confirmHandler: () => void;
}

export const PostageExpenseImport: React.FC<PostageExpenseImportProps> = () => {
  const { showNotification } = useNotification();

  // State
  const [selectedVendor, setSelectedVendor] = useState("usps");
  const [selectedImportType, setSelectedImportType] = useState("expense");
  const [file, setFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [uploadSuccess, setUploadSuccess] = useState(false);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [showConfirmationModal, setShowConfirmationModal] = useState(false);

  // Dialog state
  const [dialogState, setDialogState] = useState<DialogState>({
    titleContent: "",
    instructionTitle: "",
    instructionItems: [],
    instructionFinalQuestion: "",
    cancelMessage: "",
    confirmMessage: "",
    cancelHandler: () => {},
    confirmHandler: () => {},
  });

  // Handlers
  const handleCloseModal = () => {
    setShowConfirmationModal(false);
  };

  const handleFileChange = (incomingFile: File) => {
    setFile(incomingFile);
  };

  const handleVendorChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newVendor = e.target.value;
    setSelectedVendor(newVendor);
  };

  const handleImportTypeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newImportType = e.target.value;
    setSelectedImportType(newImportType);
  };

  const resetUploadState = () => {
    setUploadError(null);
    setUploadSuccess(false);
    setUploadProgress(0);
  };

  const handleUploadSuccess = () => {
    setUploadSuccess(true);
    setFile(null);
    showNotification("Success", "File successfully uploaded!", "success");
  };

  const handleUploadError = async (error: AxiosError) => {
    let errorMessage = "An error occurred.";
    if (error.response) {
      errorMessage = await extractErrorMessage(error.response as AxiosResponse);
    }
    toast.error(errorMessage);
    setUploadError(errorMessage);

    setDialogState({
      titleContent: "Upload Error",
      instructionTitle: errorMessage,
      instructionItems: [],
      instructionFinalQuestion: "",
      cancelMessage: "Close",
      confirmMessage: "",
      cancelHandler: handleCloseModal,
      confirmHandler: () => {},
    });
    setShowConfirmationModal(true);
  };

  const handleUpload = async () => {
    if (!file || !selectedVendor || !selectedImportType) {
      toast.error("Please fill in all required fields.");
      return;
    }

    const uploadPostageExpenseDTO = {
      vendor: selectedVendor,
      type: selectedImportType,
      file,
    };

    try {
      resetUploadState();
      setIsUploading(true);
      await uploadPostageExpense(uploadPostageExpenseDTO, (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / (progressEvent.total || 1),
        );
        setUploadProgress(percentCompleted);
      });
      handleUploadSuccess();
    } catch (err) {
      await handleUploadError(err as AxiosError);
    } finally {
      setIsUploading(false);
    }
  };

  // Scroll to status on upload changes
  useEffect(() => {
    if (isUploading || uploadSuccess || uploadError) {
      document
        .getElementById("upload-status")
        ?.scrollIntoView({ behavior: "smooth" });
    }
  }, [isUploading, uploadSuccess, uploadError]);

  // Render functions
  const renderDataSourceSelector = () => (
    <label className="block mb-4">
      <h2 className="text-gray-600 font-medium mb-2">Select Data Source</h2>
      <select
        className="p-2 mt-2 border rounded w-72 shadow-sm focus:ring-blue-500 focus:border-blue-500"
        onChange={handleVendorChange}
        value={selectedVendor}
      >
        {POSTAL_VENDORS.map((vendor) => (
          <option key={vendor.value} value={vendor.value}>
            {vendor.label}
          </option>
        ))}
      </select>
    </label>
  );

  const renderImportTypeSelector = () => (
    <div className="mb-6">
      <h2 className="text-gray-600 font-medium mb-2">Import Type</h2>
      {IMPORT_TYPES.map((type) => (
        <label className="flex items-start mb-3" key={type.value}>
          <input
            checked={selectedImportType === type.value}
            className="mt-1 mr-3 accent-blue-500"
            name="importType"
            onChange={handleImportTypeChange}
            type="radio"
            value={type.value}
          />
          <div>
            <span className="font-medium text-gray-700">{type.label}</span>
            {type.description && (
              <p className="text-sm text-gray-500">{type.description}</p>
            )}
          </div>
        </label>
      ))}
    </div>
  );

  const renderFileUploader = () => (
    <>
      <FileUploader
        handleChange={handleFileChange}
        maxSize={100}
        name="file"
        types={FILE_TYPES}
      />
      {file && (
        <p className="mt-4 text-sm text-gray-600">
          Selected file: <span className="font-medium">{file.name}</span>
        </p>
      )}
    </>
  );

  const renderUploadStatus = () => (
    <div className="mt-6" id="upload-status">
      {isUploading && (
        <>
          <div className="w-full bg-gray-200 rounded-full h-2.5">
            <div
              className="bg-blue-500 h-2.5 rounded-full"
              style={{ width: `${uploadProgress}%` }}
            />
          </div>
          <p className="mt-2 text-sm text-gray-500">
            {uploadProgress}% Complete
          </p>
        </>
      )}
    </div>
  );

  return (
    <MainPageWrapper
      error={null}
      loading={false}
      title="Postage Expense Import"
    >
      <div>
        <p className="mb-6 text-gray-700">Import postage data for billing.</p>

        {renderDataSourceSelector()}
        {renderImportTypeSelector()}
        {renderFileUploader()}
        <Button
          className="mt-3 p-3"
          disabled={!file || isUploading}
          onClick={handleUpload}
          type="button"
          variant="default"
        >
          {isUploading ? "Uploading..." : "Upload"}
        </Button>
        {renderUploadStatus()}
      </div>

      <ConfirmationDialog
        cancelMessage={dialogState.cancelMessage}
        confirmMessage={dialogState.confirmMessage}
        dialogInstructionFinalQuestion={dialogState.instructionFinalQuestion}
        dialogInstructionItems={dialogState.instructionItems}
        dialogInstructionTitle={dialogState.instructionTitle}
        dialogTitleContent={dialogState.titleContent}
        isOpen={showConfirmationModal}
        onClose={dialogState.cancelHandler}
        onConfirm={dialogState.confirmHandler}
      />
    </MainPageWrapper>
  );
};
