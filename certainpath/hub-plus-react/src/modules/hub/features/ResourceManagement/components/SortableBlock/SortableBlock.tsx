import React, { useCallback, useState } from "react";
import { useSortable, UseSortableArguments } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { RichTextEditor } from "@/modules/hub/features/ResourceManagement/components/RichTextEditor/RichTextEditor";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { SortableBlockProps } from "./types";
import { VideoEmbed } from "@/modules/hub/features/ResourceLibrary/components/VideoEmbed/VideoEmbed";
import FilePickerDialog from "@/modules/hub/features/FileManagement/components/FilePickerDialog/FilePickerDialog";
import { getPresignedUrls } from "@/modules/hub/features/FileManagement/api/getPresignedUrls/getPresignedUrlsApi";
import { useNotification } from "@/context/NotificationContext";
import {
  Upload,
  RefreshCw,
  Trash2,
  Image as ImageIcon,
  File,
  FileText,
  Video,
  Music,
  Package,
  Download,
  ExternalLink,
  Loader2,
} from "lucide-react";

export function SortableBlock({
  id,
  type,
  content,
  title,
  shortDescription,
  onRemove,
  onChange,
}: SortableBlockProps) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: id as UseSortableArguments["id"] });

  const { showNotification } = useNotification();

  // File picker dialog states
  const [isImagePickerOpen, setIsImagePickerOpen] = useState(false);
  const [isFilePickerOpen, setIsFilePickerOpen] = useState(false);

  // Loading states
  const [isLoadingImageUrl, setIsLoadingImageUrl] = useState(false);
  const [isLoadingFileUrl, setIsLoadingFileUrl] = useState(false);

  // Track filenames
  const [imageFileName, setImageFileName] = useState<string>("");
  const [fileName, setFileName] = useState<string>("");

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  };

  // Get file icon based on file extension or title
  const getFileIcon = () => {
    const iconProps = { size: 20, className: "text-gray-500" };
    const filename = title || fileName || "";
    const ext = filename.split(".").pop()?.toLowerCase();

    switch (ext) {
      case "jpg":
      case "jpeg":
      case "png":
      case "gif":
      case "webp":
        return <ImageIcon {...iconProps} className="text-blue-500" />;
      case "mp4":
      case "avi":
      case "mov":
      case "mkv":
        return <Video {...iconProps} className="text-purple-500" />;
      case "mp3":
      case "wav":
      case "ogg":
      case "m4a":
        return <Music {...iconProps} className="text-pink-500" />;
      case "pdf":
        return <FileText {...iconProps} className="text-red-500" />;
      case "doc":
      case "docx":
      case "txt":
        return <FileText {...iconProps} className="text-blue-600" />;
      case "zip":
      case "rar":
      case "7z":
      case "tar":
        return <Package {...iconProps} className="text-yellow-600" />;
      default:
        return <File {...iconProps} />;
    }
  };

  // Handle image selection from file picker
  const handleImageSelected = useCallback(
    async (
      files: Array<{ fileUuid: string; fileUrl: string; name: string }>,
    ) => {
      if (files.length === 0) return;

      const file = files[0]; // Take only the first file

      try {
        setIsLoadingImageUrl(true);

        // Get presigned URL for the selected file
        const response = await getPresignedUrls({
          fileUuids: [file.fileUuid],
        });

        const presignedUrl = response.data.presignedUrls[file.fileUuid];

        if (presignedUrl) {
          // Update the block with the new image URL and UUID
          onChange({
            content: presignedUrl,
            fileUuid: file.fileUuid,
            fileId: null, // Clear any old ID-based reference
          });
          setImageFileName(file.name);
        } else {
          showNotification(
            "Error",
            "Could not get URL for the selected image. Please try again.",
            "error",
          );
        }
      } catch (error) {
        console.error("Error getting presigned URL:", error);
        showNotification(
          "Error",
          "Failed to load the image. Please try again.",
          "error",
        );
      } finally {
        setIsLoadingImageUrl(false);
        setIsImagePickerOpen(false);
      }
    },
    [onChange, showNotification],
  );

  // Handle file selection from file picker
  const handleFileSelected = useCallback(
    async (
      files: Array<{ fileUuid: string; fileUrl: string; name: string }>,
    ) => {
      if (files.length === 0) return;

      const file = files[0]; // Take only the first file

      try {
        setIsLoadingFileUrl(true);

        // Get presigned URL for the selected file
        const response = await getPresignedUrls({
          fileUuids: [file.fileUuid],
        });

        const presignedUrl = response.data.presignedUrls[file.fileUuid];

        if (presignedUrl) {
          // Update the block with the new file URL and UUID
          onChange({
            content: presignedUrl,
            fileUuid: file.fileUuid,
            fileId: null, // Clear any old ID-based reference
            // If there's no title yet, use the filename
            title: title || file.name,
          });
          setFileName(file.name);
        } else {
          showNotification(
            "Error",
            "Could not get URL for the selected file. Please try again.",
            "error",
          );
        }
      } catch (error) {
        console.error("Error getting presigned URL:", error);
        showNotification(
          "Error",
          "Failed to load the file. Please try again.",
          "error",
        );
      } finally {
        setIsLoadingFileUrl(false);
        setIsFilePickerOpen(false);
      }
    },
    [onChange, showNotification, title],
  );

  const handleRemoveImage = useCallback(() => {
    onChange({
      content: "",
      fileUuid: null,
      fileId: null,
    });
    setImageFileName("");
  }, [onChange]);

  const handleRemoveFile = useCallback(() => {
    onChange({
      content: "",
      fileUuid: null,
      fileId: null,
    });
    setFileName("");
  }, [onChange]);

  const handleRichTextChange = useCallback(
    (newContent: string) => {
      onChange({ content: newContent });
    },
    [onChange],
  );

  const handleVimeoChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      onChange({ content: e.target.value });
    },
    [onChange],
  );

  const handleYouTubeChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      onChange({ content: e.target.value });
    },
    [onChange],
  );

  const handleTitleChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      onChange({ title: e.target.value });
    },
    [onChange],
  );

  const handleShortDescriptionChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      onChange({ shortDescription: e.target.value });
    },
    [onChange],
  );

  const renderTitleAndDescriptionFields = () => (
    <div className="space-y-3 mb-4">
      <div>
        <label className="block mb-1 text-sm font-medium text-gray-700">
          Title (optional)
        </label>
        <Input
          onChange={handleTitleChange}
          placeholder="Enter block title..."
          value={title || ""}
        />
      </div>
      <div>
        <label className="block mb-1 text-sm font-medium text-gray-700">
          Short Description (optional)
        </label>
        <Input
          onChange={handleShortDescriptionChange}
          placeholder="Enter short description..."
          value={shortDescription || ""}
        />
      </div>
    </div>
  );

  const renderBlockContent = () => {
    switch (type) {
      case "text":
        return (
          <div className="mt-2">
            <RichTextEditor
              id={id || ""}
              initialContent={content}
              onChange={handleRichTextChange}
              onRemove={onRemove}
            />
          </div>
        );

      case "image":
        return (
          <div className="mt-4 space-y-4">
            {renderTitleAndDescriptionFields()}

            {isLoadingImageUrl ? (
              <div className="flex items-center justify-center p-6 border border-dashed rounded-md">
                <Loader2 className="h-6 w-6 animate-spin text-gray-500 mr-2" />
                <span className="text-gray-500">Loading image...</span>
              </div>
            ) : content ? (
              <div className="relative group max-w-full">
                <div className="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                  <div className="relative">
                    <img
                      alt={title || "Content block image"}
                      className="w-full h-auto max-h-96 object-contain bg-gray-50"
                      src={content}
                    />
                    <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200" />
                  </div>
                  <div className="p-4 bg-white border-t border-gray-200">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2 text-sm text-gray-600">
                        <ImageIcon className="text-blue-500" size={16} />
                        <span className="truncate max-w-[300px]">
                          {imageFileName || title || "Block image"}
                        </span>
                      </div>
                      <div className="flex items-center gap-1">
                        <Button
                          className="opacity-0 group-hover:opacity-100 transition-opacity"
                          onClick={() => setIsImagePickerOpen(true)}
                          size="sm"
                          type="button"
                          variant="ghost"
                        >
                          <RefreshCw className="mr-1" size={14} />
                          Replace
                        </Button>
                        <Button
                          className="opacity-0 group-hover:opacity-100 transition-opacity"
                          onClick={handleRemoveImage}
                          size="sm"
                          type="button"
                          variant="ghost"
                        >
                          <Trash2 className="mr-1 text-red-500" size={14} />
                          Remove
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ) : (
              <button
                className="w-full border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 hover:bg-gray-50 transition-all cursor-pointer group"
                onClick={() => setIsImagePickerOpen(true)}
                type="button"
              >
                <Upload className="mx-auto h-10 w-10 text-gray-400 group-hover:text-gray-500 transition-colors mb-3" />
                <p className="text-sm font-medium text-gray-700 mb-1">
                  Click to select image
                </p>
                <p className="text-xs text-gray-500">
                  Browse from File Manager
                </p>
              </button>
            )}
          </div>
        );

      case "file":
        return (
          <div className="mt-4 space-y-4">
            {renderTitleAndDescriptionFields()}

            {isLoadingFileUrl ? (
              <div className="flex items-center justify-center p-4">
                <Loader2 className="h-4 w-4 animate-spin text-gray-500 mr-2" />
                <span className="text-sm text-gray-500">Loading file...</span>
              </div>
            ) : content ? (
              <div className="relative group bg-white border border-gray-200 rounded-lg p-4 hover:border-gray-300 hover:shadow-sm transition-all">
                <div className="flex items-start gap-3">
                  <div className="flex-shrink-0 mt-0.5">{getFileIcon()}</div>
                  <div className="flex-1 min-w-0">
                    <p
                      className="text-sm font-medium text-gray-900 truncate"
                      title={title || fileName}
                    >
                      {title || fileName || "File"}
                    </p>
                    {shortDescription && (
                      <p className="text-xs text-gray-500 mt-1">
                        {shortDescription}
                      </p>
                    )}
                    <div className="flex items-center gap-3 mt-2">
                      <a
                        className="text-xs text-gray-500 hover:text-blue-600 transition-colors inline-flex items-center gap-1"
                        href={content}
                        onClick={(e) => e.stopPropagation()}
                        rel="noopener noreferrer"
                        target="_blank"
                      >
                        <ExternalLink size={12} />
                        View
                      </a>
                      <span className="text-gray-300">•</span>
                      <a
                        className="text-xs text-gray-500 hover:text-blue-600 transition-colors inline-flex items-center gap-1"
                        download
                        href={content}
                        onClick={(e) => e.stopPropagation()}
                      >
                        <Download size={12} />
                        Download
                      </a>
                    </div>
                  </div>
                  <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Button
                      onClick={() => setIsFilePickerOpen(true)}
                      size="sm"
                      type="button"
                      variant="ghost"
                    >
                      <RefreshCw size={14} />
                    </Button>
                    <Button
                      onClick={handleRemoveFile}
                      size="sm"
                      type="button"
                      variant="ghost"
                    >
                      <Trash2 className="text-red-500" size={14} />
                    </Button>
                  </div>
                </div>
              </div>
            ) : (
              <button
                className="w-full border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 hover:bg-gray-50 transition-all cursor-pointer group"
                onClick={() => setIsFilePickerOpen(true)}
                type="button"
              >
                <Upload className="mx-auto h-10 w-10 text-gray-400 group-hover:text-gray-500 transition-colors mb-3" />
                <p className="text-sm font-medium text-gray-700 mb-1">
                  Click to select file
                </p>
                <p className="text-xs text-gray-500">
                  Browse from File Manager
                </p>
              </button>
            )}
          </div>
        );

      case "vimeo":
        return (
          <div className="mt-4 space-y-4">
            {renderTitleAndDescriptionFields()}
            <div>
              <label className="block mb-1 text-sm font-medium text-gray-700">
                Vimeo Video ID or Link:
              </label>
              <Input
                onChange={handleVimeoChange}
                placeholder="e.g., 123456789 or full Vimeo link"
                value={content}
              />
            </div>

            {content ? (
              <div className="relative aspect-video rounded-lg overflow-hidden bg-black">
                <VideoEmbed contentUrl={content.trim()} />
              </div>
            ) : (
              <p className="text-gray-500 text-sm">No Vimeo ID or link yet.</p>
            )}
          </div>
        );

      case "youtube":
        return (
          <div className="mt-4 space-y-4">
            {renderTitleAndDescriptionFields()}
            <div>
              <label className="block mb-1 text-sm font-medium text-gray-700">
                YouTube Video ID or Link:
              </label>
              <Input
                onChange={handleYouTubeChange}
                placeholder="e.g., dQw4w9WgXcQ or full YouTube link"
                value={content}
              />
            </div>

            {content ? (
              <div className="relative aspect-video rounded-lg overflow-hidden bg-black">
                <VideoEmbed contentUrl={content.trim()} />
              </div>
            ) : (
              <p className="text-gray-500 text-sm">
                No YouTube ID or link yet.
              </p>
            )}
          </div>
        );

      default:
        return <p>Unknown block type: {type}</p>;
    }
  };

  return (
    <>
      <div
        className="border rounded-lg p-4 bg-white mb-6"
        ref={setNodeRef}
        style={style}
        {...attributes}
      >
        <div className="flex items-center justify-between mb-2">
          <div className="flex items-center gap-2">
            <button
              className="cursor-grab hover:bg-gray-100 p-1 rounded"
              type="button"
              {...listeners}
              aria-label="Drag block"
            >
              ⋮⋮
            </button>
            <span className="font-medium capitalize">{type} Block</span>
          </div>
          <Button
            className="text-red-500 hover:text-red-700"
            onClick={onRemove}
            size="sm"
            type="button"
            variant="ghost"
          >
            Remove
          </Button>
        </div>

        {renderBlockContent()}
      </div>

      {/* File Picker Dialog for Images */}
      <FilePickerDialog
        allowedFileTypes={[
          "image/jpeg",
          "image/png",
          "image/gif",
          "image/webp",
          "image/svg+xml",
        ]}
        isOpen={isImagePickerOpen}
        multiSelect={false}
        onClose={() => setIsImagePickerOpen(false)}
        onSelect={handleImageSelected}
        title="Select Image"
      />

      {/* File Picker Dialog for Files */}
      <FilePickerDialog
        isOpen={isFilePickerOpen}
        multiSelect={false}
        onClose={() => setIsFilePickerOpen(false)}
        onSelect={handleFileSelected}
        title="Select File"
      />
    </>
  );
}
