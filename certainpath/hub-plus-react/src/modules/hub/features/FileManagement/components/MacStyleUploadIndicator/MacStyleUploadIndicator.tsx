// src/modules/hub/features/FileManagement/components/MacStyleUploadIndicator/MacStyleUploadIndicator.tsx
import React from "react";
import { X, Upload } from "lucide-react";
import styles from "./MacStyleUploadIndicator.module.css";

export interface FileProgress {
  file: File;
  progress: number;
  status: "pending" | "uploading" | "success" | "error";
}

interface MacStyleUploadIndicatorProps {
  uploadProgress: FileProgress[];
  setUploadProgress: (progress: FileProgress[]) => void;
}

const MacStyleUploadIndicator: React.FC<MacStyleUploadIndicatorProps> = ({
  uploadProgress,
  setUploadProgress,
}) => {
  if (uploadProgress.length === 0) return null;

  // Calculate overall progress percentage
  const overallProgress =
    uploadProgress.reduce((sum, item) => sum + item.progress, 0) /
    uploadProgress.length;

  // Count files by status
  const completedFiles = uploadProgress.filter(
    (item) => item.status === "success",
  ).length;
  const errorFiles = uploadProgress.filter(
    (item) => item.status === "error",
  ).length;

  // Determine status
  const isComplete = completedFiles === uploadProgress.length;
  const hasErrors = errorFiles > 0;

  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <div className={styles.headerInfo}>
          <div className={styles.iconContainer}>
            <Upload className={styles.icon} size={14} />
          </div>
          <h4 className={styles.title}>
            {isComplete
              ? "Upload Complete"
              : hasErrors
                ? "Upload Problem"
                : "Uploading Files"}
          </h4>
        </div>
        <button
          className={styles.closeButton}
          onClick={() => setUploadProgress([])}
          type="button"
        >
          <X className={styles.closeIcon} size={12} />
        </button>
      </div>

      <div className={styles.content}>
        <div className={styles.statusLine}>
          {isComplete ? (
            <span className={styles.statusComplete}>
              {uploadProgress.length}{" "}
              {uploadProgress.length === 1 ? "item" : "items"} uploaded
            </span>
          ) : (
            <span className={styles.status}>
              {completedFiles} of {uploadProgress.length}{" "}
              {uploadProgress.length === 1 ? "item" : "items"}
              {hasErrors ? " • Issues found" : ""}
            </span>
          )}
          <span className={styles.percentage}>
            {Math.round(overallProgress)}%
          </span>
        </div>

        <div className={styles.progressBarContainer}>
          <div
            className={`${styles.progressBar} ${isComplete ? styles.progressComplete : hasErrors ? styles.progressError : ""}`}
            style={{ width: `${overallProgress}%` }}
          />
        </div>

        <div className={styles.fileList}>
          {uploadProgress.map((item, index) => (
            <div
              className={`${styles.fileItem} ${
                item.status === "success"
                  ? styles.fileComplete
                  : item.status === "error"
                    ? styles.fileError
                    : ""
              }`}
              key={index}
            >
              <div className={styles.fileName}>{item.file.name}</div>
              <div className={styles.fileStatus}>
                {item.status === "success"
                  ? "Complete"
                  : item.status === "error"
                    ? "Failed"
                    : `${item.progress}%`}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default MacStyleUploadIndicator;
