// src/modules/hub/features/FileManagement/components/UploadProgressIndicator/UploadProgressIndicator.tsx
import React from "react";
import { X } from "lucide-react";
import { Button } from "@/components/ui/button";

export interface FileProgress {
  file: File;
  progress: number;
  status: "pending" | "uploading" | "success" | "error";
}

interface UploadProgressIndicatorProps {
  uploadProgress: FileProgress[];
  setUploadProgress: (progress: FileProgress[]) => void;
}

const UploadProgressIndicator: React.FC<UploadProgressIndicatorProps> = ({
  uploadProgress,
  setUploadProgress,
}) => {
  if (uploadProgress.length === 0) return null;

  return (
    <div className="fixed bottom-4 right-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 min-w-80 max-w-md z-50">
      <div className="flex items-center justify-between mb-2">
        <h4 className="font-medium">
          Uploading {uploadProgress.length} file(s)
        </h4>
        <Button
          className="h-6 w-6"
          onClick={() => setUploadProgress([])}
          size="icon"
          variant="ghost"
        >
          <X className="h-4 w-4" />
        </Button>
      </div>
      <div className="space-y-2 max-h-48 overflow-auto">
        {uploadProgress.map((item, index) => (
          <div className="text-sm" key={index}>
            <div className="flex items-center justify-between mb-1">
              <span className="truncate flex-1">{item.file.name}</span>
              <span className="text-xs text-gray-500 ml-2">
                {item.status === "success"
                  ? "✓"
                  : item.status === "error"
                    ? "✗"
                    : `${item.progress}%`}
              </span>
            </div>
            <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
              <div
                className={`h-1.5 rounded-full transition-all ${
                  item.status === "success"
                    ? "bg-green-500"
                    : item.status === "error"
                      ? "bg-red-500"
                      : "bg-blue-500"
                }`}
                style={{ width: `${item.progress}%` }}
              />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default UploadProgressIndicator;
