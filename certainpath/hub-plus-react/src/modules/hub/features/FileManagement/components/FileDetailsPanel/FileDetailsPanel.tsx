// src/modules/hub/features/FileManagement/components/FileDetailsPanel/FileDetailsPanel.tsx
import React, { useState, useEffect } from "react";
import { FilesystemNode, Tag } from "../../api/listFolderContents/types";
import { FilesystemNodeDetails } from "../../api/getFileSystemNodeDetails/types";
import { getFileSystemNodeDetails } from "../../api/getFileSystemNodeDetails/getFileSystemNodeDetailsApi";
import { formatFileSize } from "../../utils/formatters";
import { formatDate } from "@/utils/dateUtils";
import {
  X,
  File,
  FileText,
  Folder,
  Download,
  Calendar,
  Clock,
  HardDrive,
  FileType as FileTypeIcon,
  MapPin,
  Tag as TagIcon,
  Loader2,
  User,
  RefreshCw,
  Copy,
  Link,
  Image as ImageIcon,
  AlertTriangle,
} from "lucide-react";
import { toast } from "@/components/ui/use-toast";
import { downloadAndSaveFilesystemNode } from "../../api/downloadFilesystemNode/downloadFilesystemNodeApi";
import styles from "./FileDetailsPanel.module.css";

interface FileDetailsPanelProps {
  isOpen: boolean;
  onClose: () => void;
  node: FilesystemNode | null;
  isMobile: boolean;
}

const FileDetailsPanel: React.FC<FileDetailsPanelProps> = ({
  isOpen,
  onClose,
  node,
  isMobile,
}) => {
  const [isDownloading, setIsDownloading] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [nodeDetails, setNodeDetails] = useState<FilesystemNodeDetails | null>(
    null,
  );

  useEffect(() => {
    async function fetchDetails() {
      if (isOpen && node && node.uuid) {
        setIsLoading(true);
        try {
          const details = await getFileSystemNodeDetails(node.uuid);
          setNodeDetails(details.data);
        } catch (error) {
          console.error("Error fetching node details:", error);
          toast({
            title: "Error loading details",
            description: "Could not load file details. Please try again.",
            variant: "destructive",
          });
        } finally {
          setIsLoading(false);
        }
      }
    }

    fetchDetails();

    // When panel closes, reset the details
    return () => {
      if (!isOpen) {
        setNodeDetails(null);
      }
    };
  }, [isOpen, node]);

  if (!node) return null;

  // Use nodeDetails if available, otherwise fall back to node
  const displayNode = nodeDetails || node;

  const getFileIcon = () => {
    if (displayNode.type === "folder")
      return <Folder className={styles.folderIcon} size={48} />;

    // Check mime type for files
    if (!displayNode.mimeType)
      return <File className={styles.defaultFileIcon} size={48} />;

    if (displayNode.mimeType.startsWith("image/"))
      return <ImageIcon className={styles.imageFileIcon} size={48} />;
    if (displayNode.mimeType.startsWith("video/"))
      return <FileText className={styles.videoFileIcon} size={48} />;
    if (displayNode.mimeType.startsWith("audio/"))
      return <FileText className={styles.audioFileIcon} size={48} />;

    return <FileText className={styles.defaultFileIcon} size={48} />;
  };

  const handleDownload = async () => {
    if (displayNode.type !== "file" || !displayNode.uuid) {
      return;
    }

    setIsDownloading(true);

    try {
      await downloadAndSaveFilesystemNode(displayNode.uuid, displayNode.name);
      toast({
        title: "Download successful",
        description: `${displayNode.name} has been downloaded.`,
      });
    } catch (error) {
      console.error("Error downloading file:", error);
      toast({
        title: "Download failed",
        description:
          "There was a problem downloading the file. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsDownloading(false);
    }
  };

  const handleCopyMd5Hash = () => {
    if (nodeDetails?.md5Hash) {
      navigator.clipboard.writeText(nodeDetails.md5Hash);
      toast({
        title: "MD5 hash copied",
        description: "The file's MD5 hash has been copied to clipboard.",
      });
    }
  };

  const formatUserName = (user: { firstName: string; lastName: string }) => {
    return `${user.firstName} ${user.lastName}`;
  };

  // Render image preview if it's an image and we have a presigned URL
  const renderImagePreview = () => {
    if (
      nodeDetails?.presignedUrl &&
      nodeDetails?.mimeType &&
      nodeDetails.mimeType.startsWith("image/")
    ) {
      return (
        <div className={styles.section}>
          <h4 className={styles.sectionTitle}>
            <ImageIcon className={styles.sectionIcon} size={16} />
            Preview
          </h4>
          <div className={styles.imagePreviewContainer}>
            <img
              alt={`Preview of ${nodeDetails.name}`}
              className={styles.imagePreview}
              src={nodeDetails.presignedUrl}
            />
          </div>
        </div>
      );
    }
    return null;
  };

  // Render duplicates section if we have duplicates info
  const renderDuplicates = () => {
    if (!nodeDetails?.duplicates || nodeDetails.duplicates.count === 0) {
      return null;
    }

    return (
      <div className={styles.section}>
        <h4 className={styles.sectionTitle}>
          <Copy className={styles.sectionIcon} size={16} />
          Duplicates ({nodeDetails.duplicates.count})
        </h4>
        <div className={styles.duplicatesCard}>
          {nodeDetails.duplicates.files.map((file) => (
            <div className={styles.duplicateItem} key={file.uuid}>
              <div className={styles.duplicateInfo}>
                <span className={styles.duplicateName}>{file.name}</span>
                <span className={styles.duplicatePath}>{file.path}</span>
              </div>
              <div className={styles.duplicateMetadata}>
                {file.fileSize !== undefined && (
                  <span className={styles.duplicateSize}>
                    {formatFileSize(file.fileSize || 0)}
                  </span>
                )}
                <span className={styles.duplicateDate}>
                  {formatDate(file.createdAt)}
                </span>
              </div>
            </div>
          ))}

          <div className={styles.duplicateWarning}>
            <AlertTriangle size={16} />
            <span>
              These duplicate files are taking up unnecessary storage space.
            </span>
          </div>
        </div>
      </div>
    );
  };

  // Render usages section if we have usage info
  const renderUsages = () => {
    if (!nodeDetails?.usages || nodeDetails.usages.count === 0) {
      return null;
    }

    return (
      <div className={styles.section}>
        <h4 className={styles.sectionTitle}>
          <Link className={styles.sectionIcon} size={16} />
          Used In ({nodeDetails.usages.count} places)
        </h4>
        <div className={styles.usagesCard}>
          {nodeDetails.usages.events.length > 0 && (
            <div className={styles.usageSection}>
              <h5 className={styles.usageTypeName}>Events</h5>
              {nodeDetails.usages.events.map((event) => (
                <div className={styles.usageItem} key={event.uuid}>
                  <span className={styles.usageName}>{event.name}</span>
                  <span className={styles.usageType}>{event.usageType}</span>
                </div>
              ))}
            </div>
          )}

          {nodeDetails.usages.resources.length > 0 && (
            <div className={styles.usageSection}>
              <h5 className={styles.usageTypeName}>Resources</h5>
              {nodeDetails.usages.resources.map((resource) => (
                <div className={styles.usageItem} key={resource.uuid}>
                  <span className={styles.usageName}>{resource.name}</span>
                  <span className={styles.usageType}>
                    {resource.usageType}
                    {resource.blockType && ` (${resource.blockType})`}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    );
  };

  const panelClasses = [
    styles.detailsPanel,
    isOpen ? styles.open : styles.closed,
    isMobile ? styles.mobilePanel : styles.desktopPanel,
  ].join(" ");

  return (
    <>
      {isOpen && isMobile && (
        <div className={styles.backdrop} onClick={onClose} />
      )}
      <div className={panelClasses}>
        <div className={styles.header}>
          <h3 className={styles.title}>File Info</h3>
          <button
            aria-label="Close"
            className={styles.closeButton}
            onClick={onClose}
            type="button"
          >
            <X className={styles.closeIcon} />
          </button>
        </div>

        <div className={styles.body}>
          {isLoading ? (
            <div className={styles.loadingContainer}>
              <Loader2 className={styles.spinningIcon} size={40} />
              <p>Loading file details...</p>
            </div>
          ) : (
            <div className={styles.content}>
              <div className={styles.fileHeader}>
                <div className={styles.iconContainer}>
                  {getFileIcon()}
                  <div className={styles.iconBackground} />
                </div>
                <div className={styles.fileInfo}>
                  <h3 className={styles.fileName}>{displayNode.name}</h3>
                  {displayNode.fileSize !== undefined && (
                    <div className={styles.fileSize}>
                      {formatFileSize(displayNode.fileSize || 0)}
                      {displayNode.type === "file" && displayNode.uuid && (
                        <button
                          className={styles.downloadButton}
                          disabled={isDownloading}
                          onClick={handleDownload}
                          type="button"
                        >
                          {isDownloading ? (
                            <Loader2
                              className={styles.spinningIcon}
                              size={14}
                            />
                          ) : (
                            <Download size={14} />
                          )}
                          {isDownloading ? "Downloading..." : "Download"}
                        </button>
                      )}
                    </div>
                  )}
                </div>
              </div>

              {/* Image preview if available */}
              {renderImagePreview()}

              {/* User Information Section */}
              <div className={styles.section}>
                <h4 className={styles.sectionTitle}>
                  <User className={styles.sectionIcon} size={16} />
                  People
                </h4>
                <div className={styles.detailsCard}>
                  {nodeDetails?.createdBy && (
                    <div className={styles.detailRow}>
                      <div className={styles.detailLabel}>
                        <Calendar size={14} />
                        Created By
                      </div>
                      <div className={styles.detailValue}>
                        {formatUserName(nodeDetails.createdBy)}
                      </div>
                    </div>
                  )}

                  {nodeDetails?.updatedBy && (
                    <div className={styles.detailRow}>
                      <div className={styles.detailLabel}>
                        <RefreshCw size={14} />
                        Last Modified By
                      </div>
                      <div className={styles.detailValue}>
                        {formatUserName(nodeDetails.updatedBy)}
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* File Information */}
              <div className={styles.section}>
                <h4 className={styles.sectionTitle}>
                  <HardDrive className={styles.sectionIcon} size={16} />
                  Details
                </h4>
                <div className={styles.detailsCard}>
                  {displayNode.fileSize !== undefined && (
                    <div className={styles.detailRow}>
                      <div className={styles.detailLabel}>
                        <HardDrive size={14} />
                        Size
                      </div>
                      <div className={styles.detailValue}>
                        {formatFileSize(displayNode.fileSize || 0)}
                      </div>
                    </div>
                  )}

                  <div className={styles.detailRow}>
                    <div className={styles.detailLabel}>
                      <Calendar size={14} />
                      Created
                    </div>
                    <div className={styles.detailValue}>
                      {formatDate(displayNode.createdAt)}
                    </div>
                  </div>

                  <div className={styles.detailRow}>
                    <div className={styles.detailLabel}>
                      <Clock size={14} />
                      Modified
                    </div>
                    <div className={styles.detailValue}>
                      {formatDate(displayNode.updatedAt)}
                    </div>
                  </div>

                  {displayNode.fileType && (
                    <div className={styles.detailRow}>
                      <div className={styles.detailLabel}>
                        <FileTypeIcon size={14} />
                        File Type
                      </div>
                      <div className={styles.detailValue}>
                        {displayNode.fileType}
                      </div>
                    </div>
                  )}

                  {nodeDetails?.md5Hash && (
                    <div className={styles.detailRowFull}>
                      <div className={styles.detailLabel}>
                        <Copy size={14} />
                        MD5 Hash
                        <button
                          className={styles.copyButton}
                          onClick={handleCopyMd5Hash}
                          type="button"
                        >
                          <Copy size={14} />
                        </button>
                      </div>
                      <div className={styles.detailValueFull}>
                        {nodeDetails.md5Hash}
                      </div>
                    </div>
                  )}

                  {displayNode.path && (
                    <div className={styles.detailRowFull}>
                      <div className={styles.detailLabel}>
                        <MapPin size={14} />
                        Path
                      </div>
                      <div className={styles.detailValueFull}>
                        {displayNode.path}
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* Duplicates Section */}
              {renderDuplicates()}

              {/* Usages Section */}
              {renderUsages()}

              {/* Tags Section - Read-only */}
              <div className={styles.section}>
                <h4 className={styles.sectionTitle}>
                  <TagIcon className={styles.sectionIcon} size={16} />
                  Tags
                </h4>

                <div className={styles.tagsCard}>
                  {displayNode.tags && displayNode.tags.length > 0 ? (
                    <div className={styles.tagsList}>
                      {displayNode.tags.map((tag: Tag) => (
                        <div
                          className={styles.tagPill}
                          key={tag.id}
                          style={{
                            backgroundColor: tag.color
                              ? `${tag.color}15`
                              : "#f3f4f6",
                            color: tag.color || "#374151",
                            borderColor: tag.color
                              ? `${tag.color}30`
                              : "#e5e7eb",
                          }}
                        >
                          <div
                            className={styles.tagDot}
                            style={{
                              backgroundColor: tag.color || "#6b7280",
                            }}
                          />
                          <span>{tag.name}</span>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className={styles.emptyTags}>
                      <p>No tags assigned to this item</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default FileDetailsPanel;
