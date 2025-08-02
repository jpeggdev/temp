import React, { useEffect, useState } from "react";
import { FileUploader } from "react-drag-drop-files";
import { toast } from "react-toastify";
import { AxiosError, AxiosResponse } from "axios";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import TagManager, { sanitizeTag } from "@/components/TagManager/TagManager";
import ConfirmationDialog from "@/components/ConfirmationDialog/ConfirmationDialog";
import { uploadProspectSource } from "@/api/uploadProspectSource/uploadProspectSourceApi";
import { extractErrorMessage } from "@/utils/extractErrorMessage";
import { useNotification } from "@/context/NotificationContext";

const FILE_TYPES = ["CSV", "XLSX", "XLS"];

const SOFTWARE_PACKAGES = [
  {
    value: "acxiom",
    label: "Acxiom List",
  },
];

const IMPORT_TYPES = [
  {
    value: "prospects",
    label: "Prospects",
    description:
      "Prospects are individuals or organizations that have the potential to become a customer.",
  },
];

interface ProspectSourceImportProps {
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

const generateDefaultTag = (software: string, importType: string): string => {
  const today = new Date().toISOString().split("T")[0];
  return sanitizeTag(`${software}_${importType}_${today}`);
};

export const ProspectSourceImport: React.FC<ProspectSourceImportProps> = () => {
  const { showNotification } = useNotification();

  // State
  const [selectedSoftware, setSelectedSoftware] = useState("acxiom");
  const [selectedImportType, setSelectedImportType] = useState("prospects");
  const [file, setFile] = useState<File | null>(null);
  const [customTags, setCustomTags] = useState<string[]>([
    generateDefaultTag("acxiom", "prospects"),
  ]);
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

  const handleCustomTagsChange = (tags: string[]) => {
    setCustomTags(tags);
  };

  const handleFileChange = (incomingFile: File) => {
    setFile(incomingFile);
  };

  const handleSoftwareChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newSoftware = e.target.value;
    setSelectedSoftware(newSoftware);
    handleCustomTagsChange([
      generateDefaultTag(newSoftware, selectedImportType),
    ]);
  };

  const handleImportTypeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newImportType = e.target.value;
    setSelectedImportType(newImportType);
    handleCustomTagsChange([
      generateDefaultTag(selectedSoftware, newImportType),
    ]);
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
    if (!file || !selectedSoftware || !selectedImportType) {
      toast.error("Please fill in all required fields.");
      return;
    }

    const uploadProspectSourceDTO = {
      file,
      software: selectedSoftware,
      importType: selectedImportType,
      tags: customTags,
    };

    try {
      resetUploadState();
      setIsUploading(true);
      await uploadProspectSource(uploadProspectSourceDTO, (progressEvent) => {
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
        onChange={handleSoftwareChange}
        value={selectedSoftware}
      >
        {SOFTWARE_PACKAGES.map((software) => (
          <option key={software.value} value={software.value}>
            {software.label}
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

  const renderTagSelector = () => (
    <label className="block mb-4">
      <h2 className="text-gray-600 font-medium">Tag Upload</h2>
      <p className="text-sm text-gray-500">
        Create descriptive tags to easily identify and retrieve this prospect
        list when creating a campaign.
      </p>
      <TagManager
        existingTags={customTags}
        maxTags={5}
        onTagsChange={handleCustomTagsChange}
      />
    </label>
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

  const renderUploadButton = () => (
    <button
      className={`mt-6 p-3 rounded-lg font-medium text-white transition ${
        isUploading
          ? "bg-gray-400 cursor-not-allowed"
          : "bg-blue-500 hover:bg-blue-600"
      }`}
      disabled={!file || isUploading}
      onClick={handleUpload}
    >
      {isUploading ? "Uploading..." : "Upload"}
    </button>
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
      title="Prospect Source Import"
    >
      <div>
        <p className="mb-6 text-gray-700">
          Import prospects from a data source like a purchased marketing list
        </p>

        {renderDataSourceSelector()}
        {renderImportTypeSelector()}
        {renderTagSelector()}
        {renderFileUploader()}
        {renderUploadButton()}
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
