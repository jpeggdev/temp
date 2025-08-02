import React, { useCallback } from "react";
import { Button } from "@/components/ui/button";
import { Input as HiddenFileInput } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Trash2 } from "lucide-react";
import { uploadTmpFile } from "@/api/uploadTmpFile/uploadTmpFileApi";
import { downloadFile } from "@/api/downloadFile/downloadFileApi";

interface EventFileRecord {
  fileId: number;
  fileUrl: string;
  fileUuid?: string;
  name: string;
}

interface EventFileUploadProps {
  fileIds: number[];
  onChangeFileIds: (newIds: number[]) => void;
  uploadedFiles: EventFileRecord[];
  onChangeUploadedFiles: (newFiles: EventFileRecord[]) => void;
  fileInputRef: React.RefObject<HTMLInputElement>;
  isUploadingFiles?: boolean;
  setIsUploadingFiles?: (val: boolean) => void;
}

function EventFileUpload({
  fileIds,
  onChangeFileIds,
  uploadedFiles,
  onChangeUploadedFiles,
  fileInputRef,
  isUploadingFiles = false,
  setIsUploadingFiles,
}: EventFileUploadProps) {
  const handleRemoveFile = useCallback(
    (fileId: number) => {
      onChangeUploadedFiles(
        uploadedFiles.filter((file) => file.fileId !== fileId),
      );
      const newIds = fileIds.filter((id) => id !== fileId);
      onChangeFileIds(newIds);
    },
    [fileIds, onChangeFileIds, uploadedFiles, onChangeUploadedFiles],
  );

  const handleDownloadFile = useCallback(
    async (fileUuid: string, fileName: string) => {
      try {
        const blob = await downloadFile({ fileUuid });
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = blobUrl;
        link.download = fileName || "download.bin";
        document.body.appendChild(link);
        link.click();

        link.remove();
        window.URL.revokeObjectURL(blobUrl);
      } catch (err) {
        console.error("File download failed:", err);
      }
    },
    [],
  );

  const onFileInputChange = useCallback(
    async (event: React.ChangeEvent<HTMLInputElement>) => {
      if (!setIsUploadingFiles) return;

      setIsUploadingFiles(true);

      const files = Array.from(event.target.files || []);
      const newFileIds: number[] = [...fileIds];

      for (const file of files) {
        try {
          const res = await uploadTmpFile(
            file,
            "cp-membership-files",
            "events",
          );
          onChangeUploadedFiles([
            ...uploadedFiles,
            {
              fileId: res.data.fileId,
              fileUrl: res.data.fileUrl,
              name: file.name,
              fileUuid: res.data.fileUuid,
            },
          ]);
          newFileIds.push(res.data.fileId);
        } catch (err) {
          console.error("Failed to upload file:", err);
        }
      }

      onChangeFileIds(newFileIds);

      if (fileInputRef.current) {
        fileInputRef.current.value = "";
      }

      setIsUploadingFiles(false);
    },
    [
      fileIds,
      onChangeFileIds,
      fileInputRef,
      onChangeUploadedFiles,
      setIsUploadingFiles,
      uploadedFiles,
    ],
  );

  return (
    <div className="flex flex-col gap-4 mt-2">
      {isUploadingFiles && (
        <div className="flex items-center gap-2">
          <div
            aria-label="loading"
            className="animate-spin inline-block w-5 h-5 border-[3px] border-current border-t-transparent text-blue-600 rounded-full"
            role="status"
          ></div>
          <span className="text-sm text-blue-600">Uploading files...</span>
        </div>
      )}

      <div className="flex items-center justify-center w-full">
        <label
          className="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:border-primary hover:bg-gray-100 transition-colors duration-200"
          htmlFor="dropzone-file"
        >
          <div className="flex flex-col items-center justify-center pt-5 pb-6">
            <svg
              aria-hidden="true"
              className="w-8 h-8 mb-4 text-gray-500"
              fill="none"
              viewBox="0 0 20 16"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"
                stroke="currentColor"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
              />
            </svg>
            <p className="mb-2 text-sm text-gray-500">
              <span className="font-semibold">Click to upload</span> or drag and
              drop
            </p>
            <p className="text-xs text-gray-500">
              PDF, DOC, DOCX, or PPT (MAX. 10MB)
            </p>
          </div>
          <HiddenFileInput
            accept=".pdf,.doc,.docx,.ppt,.pptx"
            className="hidden"
            id="dropzone-file"
            multiple
            onChange={onFileInputChange}
            ref={fileInputRef}
            type="file"
          />
        </label>
      </div>

      {uploadedFiles.length > 0 && (
        <div className="space-y-2">
          <Label>Uploaded Files</Label>
          {uploadedFiles.map((f) => (
            <div
              className="flex items-center justify-between p-2 bg-gray-50 rounded-md"
              key={f.fileId}
            >
              <span className="text-sm truncate max-w-[60%]">{f.name}</span>
              <div className="flex items-center gap-2">
                {f.fileUuid && f.fileUuid.length > 0 && (
                  <Button
                    onClick={() => handleDownloadFile(f.fileUuid!, f.name)}
                    size="sm"
                    type="button"
                    variant="secondary"
                  >
                    Download
                  </Button>
                )}

                <Button
                  onClick={() => handleRemoveFile(f.fileId)}
                  size="sm"
                  type="button"
                  variant="ghost"
                >
                  <Trash2 className="h-4 w-4 text-red-500" />
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default EventFileUpload;
