import React, { Fragment, useCallback, useState } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { useDropzone, FileRejection } from "react-dropzone";
import {
  Upload,
  X,
  FileText,
  Image,
  Film,
  Music,
  File,
  AlertCircle,
} from "lucide-react";
import styles from "./SimpleFileUpload.module.css";

interface SimpleFileUploadProps {
  isOpen: boolean;
  onClose: () => void;
  onUpload: (files: File[]) => void;
  currentFolderId?: string | null;
  maxFileSize?: number; // In bytes
}

// Helper function to format file sizes
const formatFileSize = (bytes: number): string => {
  if (!bytes) return "";
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
  if (bytes < 1024 * 1024 * 1024)
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
  return (bytes / (1024 * 1024 * 1024)).toFixed(1) + " GB";
};

// Helper to determine file type icon
const getFileIcon = (file: File) => {
  const fileType = file.type;

  if (fileType.startsWith("image/")) {
    return <Image className={styles.fileTypeIcon} size={18} />;
  } else if (fileType.startsWith("video/")) {
    return <Film className={styles.fileTypeIcon} size={18} />;
  } else if (fileType.startsWith("audio/")) {
    return <Music className={styles.fileTypeIcon} size={18} />;
  } else if (fileType.startsWith("text/")) {
    return <FileText className={styles.fileTypeIcon} size={18} />;
  } else {
    return <File className={styles.fileTypeIcon} size={18} />;
  }
};

const SimpleFileUpload: React.FC<SimpleFileUploadProps> = ({
  isOpen,
  onClose,
  onUpload,
  maxFileSize = 100 * 1024 * 1024, // 100MB default
}) => {
  const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
  const [errors, setErrors] = useState<string[]>([]);

  const onDrop = useCallback(
    (acceptedFiles: File[], rejectedFiles: FileRejection[]) => {
      // Handle accepted files
      const newFiles = acceptedFiles.filter((file) => {
        // Check for duplicates
        const isDuplicate = selectedFiles.some(
          (existing) =>
            existing.name === file.name && existing.size === file.size,
        );

        if (isDuplicate) {
          setErrors((prev) => [...prev, `${file.name} is already added`]);
          return false;
        }

        // Check file size
        if (file.size > maxFileSize) {
          setErrors((prev) => [
            ...prev,
            `${file.name} exceeds maximum size of ${formatFileSize(maxFileSize)}`,
          ]);
          return false;
        }

        return true;
      });

      setSelectedFiles((prev) => [...prev, ...newFiles]);

      // Handle rejected files
      rejectedFiles.forEach((rejection) => {
        const { file, errors } = rejection;
        errors.forEach((error) => {
          if (error.code === "file-too-large") {
            setErrors((prev) => [...prev, `${file.name} is too large`]);
          } else if (error.code === "file-invalid-type") {
            setErrors((prev) => [
              ...prev,
              `${file.name} has an invalid file type`,
            ]);
          } else {
            setErrors((prev) => [...prev, `${file.name}: ${error.message}`]);
          }
        });
      });
    },
    [selectedFiles, maxFileSize],
  );

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    maxSize: maxFileSize,
  });

  const removeFile = (index: number): void => {
    setSelectedFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const clearErrors = (): void => {
    setErrors([]);
  };

  const handleUpload = (): void => {
    if (selectedFiles.length === 0) return;
    onUpload(selectedFiles);
    setSelectedFiles([]);
    setErrors([]);
  };

  const handleClose = (): void => {
    setSelectedFiles([]);
    setErrors([]);
    onClose();
  };

  const totalSize: number = selectedFiles.reduce(
    (sum, file) => sum + file.size,
    0,
  );

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog as="div" className={styles.dialogWrapper} onClose={handleClose}>
        {/* Backdrop */}
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className={styles.backdrop} />
        </Transition.Child>

        {/* Dialog positioning wrapper */}
        <div className={styles.fixedWrapper}>
          <div className={styles.dialogContainer}>
            {/* Dialog panel with animation */}
            <Transition.Child
              as={Fragment}
              enter="ease-out duration-300"
              enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enterTo="opacity-100 translate-y-0 sm:scale-100"
              leave="ease-in duration-200"
              leaveFrom="opacity-100 translate-y-0 sm:scale-100"
              leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
              <Dialog.Panel className={styles.dialogPanel}>
                {/* Header */}
                <div className={styles.dialogHeader}>
                  <Dialog.Title as="h3" className={styles.dialogTitle}>
                    <Upload className={styles.titleIcon} size={20} />
                    Upload Files
                  </Dialog.Title>
                  <button
                    className={styles.closeButton}
                    onClick={handleClose}
                    type="button"
                  >
                    <X size={16} />
                  </button>
                </div>

                {/* Content */}
                <div className={styles.dialogContent}>
                  {/* Dropzone */}
                  <div
                    {...getRootProps()}
                    className={`${styles.dropzone} ${
                      isDragActive ? styles.dropzoneActive : ""
                    }`}
                  >
                    <input {...getInputProps()} />
                    <Upload className={styles.uploadIcon} size={48} />
                    {isDragActive ? (
                      <p className={styles.dropzoneTextActive}>
                        Drop the files here...
                      </p>
                    ) : (
                      <>
                        <p className={styles.dropzoneText}>
                          Drag & drop files here, or click to select
                        </p>
                        <p className={styles.dropzoneSubtext}>
                          Maximum file size: {formatFileSize(maxFileSize)}
                        </p>
                      </>
                    )}
                  </div>

                  {/* Selected files */}
                  {selectedFiles.length > 0 && (
                    <div className={styles.selectedFilesContainer}>
                      <div className={styles.selectedFilesHeader}>
                        <h4 className={styles.selectedFilesTitle}>
                          Selected files ({selectedFiles.length})
                        </h4>
                        <span className={styles.selectedFilesTotal}>
                          Total: {formatFileSize(totalSize)}
                        </span>
                      </div>

                      <div className={styles.fileListContainer}>
                        <div className={styles.fileList}>
                          {selectedFiles.map((file, index) => (
                            <div className={styles.fileItem} key={index}>
                              <div className={styles.fileIconContainer}>
                                {getFileIcon(file)}
                              </div>
                              <div className={styles.fileDetails}>
                                <p
                                  className={styles.fileName}
                                  title={file.name}
                                >
                                  {file.name}
                                </p>
                                <p className={styles.fileSize}>
                                  {formatFileSize(file.size)}
                                </p>
                              </div>
                              <button
                                className={styles.removeFileButton}
                                onClick={() => removeFile(index)}
                                type="button"
                              >
                                <X size={16} />
                              </button>
                            </div>
                          ))}
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Error messages */}
                  {errors.length > 0 && (
                    <div className={styles.errorsContainer}>
                      <div className={styles.errorsHeader}>
                        <div className={styles.errorsTitle}>
                          <AlertCircle size={16} />
                          Upload errors
                        </div>
                        <button
                          className={styles.clearErrorsButton}
                          onClick={clearErrors}
                          type="button"
                        >
                          Clear all
                        </button>
                      </div>
                      <ul className={styles.errorsList}>
                        {errors.map((error, index) => (
                          <li className={styles.errorItem} key={index}>
                            • {error}
                          </li>
                        ))}
                      </ul>
                    </div>
                  )}
                </div>

                {/* Footer */}
                <div className={styles.buttonGroup}>
                  <button
                    className={styles.cancelButton}
                    onClick={handleClose}
                    type="button"
                  >
                    Cancel
                  </button>

                  <button
                    className={`${styles.uploadButton} ${
                      selectedFiles.length === 0
                        ? styles.uploadButtonDisabled
                        : ""
                    }`}
                    disabled={selectedFiles.length === 0}
                    onClick={handleUpload}
                    type="button"
                  >
                    <Upload className={styles.buttonIcon} size={16} />
                    Upload{" "}
                    {selectedFiles.length > 0 && `(${selectedFiles.length})`}
                  </button>
                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition.Root>
  );
};

export default SimpleFileUpload;
