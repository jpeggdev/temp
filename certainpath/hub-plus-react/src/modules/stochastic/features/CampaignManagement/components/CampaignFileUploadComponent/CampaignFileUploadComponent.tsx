import React, { useCallback, useState, useEffect } from "react";
import { useDropzone } from "react-dropzone";
import { uploadCampaignFile } from "../../../../../../api/uploadCampaignFile/uploadCampaignFileApi";
import { AxiosError } from "axios";

interface CampaignFileUploadProps {
  campaignId: number;
}

const CampaignFileUploadComponent: React.FC<CampaignFileUploadProps> = ({
  campaignId,
}) => {
  const [uploading, setUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState<number>(0);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [uploadSuccess, setUploadSuccess] = useState<boolean>(false);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  const onDrop = useCallback(
    async (acceptedFiles: File[]) => {
      if (acceptedFiles.length === 0) return;

      const file = acceptedFiles[0];
      setUploadError(null);
      setUploadSuccess(false);
      setSelectedFile(file);

      try {
        setUploading(true);
        setUploadProgress(0);

        await uploadCampaignFile(campaignId, file, (progressEvent) => {
          const percentCompleted = Math.round(
            (progressEvent.loaded * 100) / (progressEvent.total || 1),
          );
          setUploadProgress(percentCompleted);
        });

        setUploadSuccess(true);
        setSelectedFile(null);
      } catch (error: unknown) {
        if (error instanceof AxiosError && error.response?.data?.error) {
          setUploadError(error.response.data.error);
        } else if (error instanceof Error) {
          setUploadError(error.message);
        } else {
          setUploadError("An error occurred during upload.");
        }
      } finally {
        setUploading(false);
      }
    },
    [campaignId],
  );

  const { getRootProps, getInputProps, isDragActive, fileRejections } =
    useDropzone({
      onDrop,
      accept: {
        "text/csv": [".csv"],
        "application/vnd.ms-excel": [".xls", ".xlsx"],
        "application/pdf": [".pdf"],
        "image/*": [".png", ".jpg", ".jpeg", ".gif"],
      },
      maxSize: 5 * 1024 * 1024,
      multiple: false,
    });

  // Scroll to the progress bar or success message when uploading starts
  useEffect(() => {
    if (uploading || uploadSuccess || uploadError) {
      document
        .getElementById("upload-status")
        ?.scrollIntoView({ behavior: "smooth" });
    }
  }, [uploading, uploadSuccess, uploadError]);

  return (
    <div className="mt-6">
      <div className="mb-4" id="upload-status">
        {uploading && (
          <div>
            <p className="text-sm text-gray-700">
              Uploading: {uploadProgress}%
            </p>
            <div className="w-full bg-gray-200 rounded-full h-2.5">
              <div
                className="bg-indigo-600 h-2.5 rounded-full"
                style={{ width: `${uploadProgress}%` }}
              ></div>
            </div>
          </div>
        )}

        {uploadSuccess && (
          <div className="text-sm text-green-600">
            File uploaded successfully!
          </div>
        )}

        {uploadError && (
          <div className="text-sm text-red-600">{uploadError}</div>
        )}
      </div>

      <div
        {...getRootProps()}
        className={`flex flex-col items-center justify-center border-2 border-dashed rounded-md p-6 cursor-pointer ${
          isDragActive ? "border-indigo-500 bg-indigo-50" : "border-gray-300"
        }`}
      >
        <input {...getInputProps()} />
        {isDragActive ? (
          <p className="text-indigo-600">Drop the file here...</p>
        ) : (
          <p className="text-gray-600">
            Drag & drop a file here, or click to select a file
          </p>
        )}
        <em className="text-xs text-gray-500 mt-2">
          Only one *.csv, *.xls, *.xlsx, *.pdf, or image file (max 5MB) is
          accepted
        </em>
      </div>

      {selectedFile && (
        <div className="mt-4">
          <h4 className="text-sm font-medium text-gray-700">Selected File</h4>
          <ul className="mt-2 text-sm text-gray-600">
            <li>
              {selectedFile.name} - {selectedFile.size} bytes
            </li>
          </ul>
        </div>
      )}

      {fileRejections.length > 0 && (
        <div className="mt-4">
          <h4 className="text-sm font-medium text-red-700">Rejected File</h4>
          <ul className="mt-2 text-sm text-red-600">
            {fileRejections.map(({ file, errors }) => (
              <li key={file.name}>
                {file.name} - {file.size} bytes
                <ul className="list-disc ml-5">
                  {errors.map((e) => (
                    <li key={e.code}>{e.message}</li>
                  ))}
                </ul>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
};

export default CampaignFileUploadComponent;
