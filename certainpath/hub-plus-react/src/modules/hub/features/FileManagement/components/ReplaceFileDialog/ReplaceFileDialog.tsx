import React, { Fragment, useCallback, useState, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { useDropzone, FileRejection } from "react-dropzone";
import {
  Upload,
  X,
  RefreshCw,
  Loader2,
  FileText,
  AlertCircle,
  ArrowRight,
} from "lucide-react";
import styles from "./ReplaceFileDialog.module.css";

interface ReplaceFileDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onReplace: (file: File) => void;
  originalFileName: string;
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

const ReplaceFileDialog: React.FC<ReplaceFileDialogProps> = ({
  isOpen,
  onClose,
  onReplace,
  originalFileName,
  maxFileSize = 100 * 1024 * 1024, // 100MB default
}) => {
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isReplacing, setIsReplacing] = useState<boolean>(false);

  // Reset state when modal closes
  useEffect(() => {
    if (!isOpen) {
      setSelectedFile(null);
      setError(null);
      setIsReplacing(false);
    }
  }, [isOpen]);

  const onDrop = useCallback(
    (acceptedFiles: File[], rejectedFiles: FileRejection[]) => {
      // Take only the first file since we're replacing a single file
      const file = acceptedFiles[0];

      if (file) {
        // Check file size
        if (file.size > maxFileSize) {
          setError(
            `File exceeds maximum size of ${formatFileSize(maxFileSize)}`,
          );
          return;
        }

        setSelectedFile(file);
        setError(null);
      }

      // Handle rejected files
      if (rejectedFiles.length > 0) {
        const { file, errors } = rejectedFiles[0];
        if (errors[0].code === "file-too-large") {
          setError(`${file.name} is too large`);
        } else if (errors[0].code === "file-invalid-type") {
          setError(`${file.name} has an invalid file type`);
        } else {
          setError(`${file.name}: ${errors[0].message}`);
        }
      }
    },
    [maxFileSize],
  );

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    maxSize: maxFileSize,
    multiple: false, // Only allow a single file
  });

  const clearSelection = (): void => {
    setSelectedFile(null);
    setError(null);
  };

  const handleReplace = async (): Promise<void> => {
    if (!selectedFile) return;

    setIsReplacing(true);
    try {
      onReplace(selectedFile); // Pass the File object
      onClose();
    } catch (error) {
      console.error("Error replacing file:", error);
      setIsReplacing(false);
    }
  };

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog as="div" className={styles.dialogWrapper} onClose={onClose}>
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
                    <RefreshCw className={styles.titleIcon} size={20} />
                    Replace File
                  </Dialog.Title>
                  <button
                    className={styles.closeButton}
                    onClick={onClose}
                    type="button"
                  >
                    <X size={16} />
                  </button>
                </div>

                {/* Content */}
                <div className={styles.dialogContent}>
                  <div className={styles.originalFileInfo}>
                    <div className={styles.fileIconContainer}>
                      <FileText className={styles.fileIcon} size={24} />
                    </div>
                    <div className={styles.fileDetails}>
                      <p className={styles.originalFileLabel}>Original file:</p>
                      <p className={styles.originalFileName}>
                        {originalFileName}
                      </p>
                    </div>
                  </div>

                  {/* Dropzone */}
                  {!selectedFile ? (
                    <div
                      {...getRootProps()}
                      className={`${styles.dropzone} ${
                        isDragActive ? styles.dropzoneActive : ""
                      }`}
                    >
                      <input {...getInputProps()} />
                      <Upload className={styles.uploadIcon} size={36} />
                      {isDragActive ? (
                        <p className={styles.dropzoneTextActive}>
                          Drop the file here...
                        </p>
                      ) : (
                        <>
                          <p className={styles.dropzoneText}>
                            Drag & drop a file here, or click to select
                          </p>
                          <p className={styles.dropzoneSubtext}>
                            Maximum file size: {formatFileSize(maxFileSize)}
                          </p>
                        </>
                      )}
                    </div>
                  ) : (
                    <div className={styles.replacePreview}>
                      <div className={styles.replaceArrow}>
                        <ArrowRight className={styles.arrowIcon} size={24} />
                      </div>

                      <div className={styles.selectedFileContainer}>
                        <div className={styles.selectedFileHeader}>
                          <h4 className={styles.selectedFileTitle}>
                            New file selected
                          </h4>
                          <button
                            className={styles.clearButton}
                            onClick={clearSelection}
                            type="button"
                          >
                            <X size={16} />
                          </button>
                        </div>

                        <div className={styles.selectedFileContent}>
                          <div className={styles.fileIconContainer}>
                            <FileText
                              className={styles.newFileIcon}
                              size={24}
                            />
                          </div>
                          <div className={styles.fileDetails}>
                            <p
                              className={styles.selectedFileName}
                              title={selectedFile.name}
                            >
                              {selectedFile.name}
                            </p>
                            <p className={styles.selectedFileSize}>
                              {formatFileSize(selectedFile.size)}
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Error message */}
                  {error && (
                    <div className={styles.errorContainer}>
                      <div className={styles.errorHeader}>
                        <div className={styles.errorTitle}>
                          <AlertCircle className={styles.errorIcon} size={16} />
                          Upload error
                        </div>
                        <button
                          className={styles.clearErrorButton}
                          onClick={() => setError(null)}
                          type="button"
                        >
                          Clear
                        </button>
                      </div>
                      <p className={styles.errorMessage}>{error}</p>
                    </div>
                  )}
                </div>

                {/* Footer */}
                <div className={styles.buttonGroup}>
                  <button
                    className={styles.cancelButton}
                    disabled={isReplacing}
                    onClick={onClose}
                    type="button"
                  >
                    Cancel
                  </button>

                  <button
                    className={`${styles.replaceButton} ${
                      !selectedFile || isReplacing
                        ? styles.replaceButtonDisabled
                        : ""
                    }`}
                    disabled={!selectedFile || isReplacing}
                    onClick={handleReplace}
                    type="button"
                  >
                    {isReplacing ? (
                      <>
                        <Loader2
                          className={`${styles.buttonIcon} ${styles.spinningIcon}`}
                          size={16}
                        />
                        Replacing...
                      </>
                    ) : (
                      <>
                        <RefreshCw className={styles.buttonIcon} size={16} />
                        Replace File
                      </>
                    )}
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

export default ReplaceFileDialog;
