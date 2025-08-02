import React from "react";

import {
  X,
  File,
  FileText,
  Image as ImageIcon,
  Video,
  Music,
  Package,
  Upload,
  Plus,
  Loader2,
} from "lucide-react";

export interface FileItem {
  uuid: string;
  name: string;
  url?: string;
  mimeType?: string;
}

interface FileCollectionDisplayProps {
  files: FileItem[];
  loading?: boolean;
  onSelectFiles: () => void;
  onRemoveFile: (fileUuid: string) => void;
  onClearAll: () => void;
}

export default function FileCollectionDisplay({
  files,
  loading,
  onSelectFiles,
  onRemoveFile,
  onClearAll,
}: FileCollectionDisplayProps) {
  // Get file icon based on mime type or file extension
  const getFileIcon = (file: FileItem) => {
    const iconProps = { size: 20, className: "text-gray-500" };

    // Check mime type first
    if (file.mimeType?.startsWith("image/")) {
      return <ImageIcon {...iconProps} className="text-blue-500" />;
    } else if (file.mimeType?.startsWith("video/")) {
      return <Video {...iconProps} className="text-purple-500" />;
    } else if (file.mimeType?.startsWith("audio/")) {
      return <Music {...iconProps} className="text-pink-500" />;
    } else if (file.mimeType === "application/pdf") {
      return <FileText {...iconProps} className="text-red-500" />;
    } else if (
      file.mimeType?.includes("zip") ||
      file.mimeType?.includes("rar") ||
      file.mimeType?.includes("7z")
    ) {
      return <Package {...iconProps} className="text-yellow-600" />;
    }

    // Check file extension as fallback
    const ext = file.name.split(".").pop()?.toLowerCase();
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

  // No files selected - show upload prompt
  if (files.length === 0 && !loading) {
    return (
      <div className="w-full">
        <button
          className="w-full border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 hover:bg-gray-50 transition-all cursor-pointer group"
          onClick={onSelectFiles}
          type="button"
        >
          <Upload className="mx-auto h-10 w-10 text-gray-400 group-hover:text-gray-500 transition-colors mb-3" />
          <p className="text-sm font-medium text-gray-700 mb-1">
            Click to add files
          </p>
          <p className="text-xs text-gray-500">
            Select multiple files from File Manager
          </p>
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {/* Files header with count and actions */}
      {files.length > 0 && (
        <div className="flex items-center justify-between">
          <h4 className="text-sm font-medium text-gray-700">
            {files.length} {files.length === 1 ? "file" : "files"} selected
          </h4>
          <div className="flex items-center gap-2">
            <button
              className="text-sm text-blue-600 hover:text-blue-700 hover:underline font-medium"
              onClick={onSelectFiles}
              type="button"
            >
              <Plus className="inline-block w-3 h-3 mr-1" />
              Add more
            </button>
            {files.length > 1 && (
              <>
                <span className="text-gray-300">|</span>
                <button
                  className="text-sm text-gray-600 hover:text-red-600 hover:underline"
                  onClick={onClearAll}
                  type="button"
                >
                  Clear all
                </button>
              </>
            )}
          </div>
        </div>
      )}

      {/* File grid */}
      {files.length > 0 && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          {files.map((file) => (
            <div
              className="relative group bg-white border border-gray-200 rounded-lg p-3 hover:border-gray-300 hover:shadow-sm transition-all"
              key={file.uuid}
            >
              <div className="flex items-start gap-3">
                <div className="flex-shrink-0 mt-0.5">{getFileIcon(file)}</div>
                <div className="flex-1 min-w-0">
                  <p
                    className="text-sm font-medium text-gray-900 truncate"
                    title={file.name}
                  >
                    {file.name}
                  </p>
                  <div className="flex items-center gap-3 mt-1">
                    {file.url && (
                      <>
                        <a
                          className="text-xs text-gray-500 hover:text-blue-600 transition-colors"
                          href={file.url}
                          onClick={(e) => e.stopPropagation()}
                          rel="noopener noreferrer"
                          target="_blank"
                        >
                          View
                        </a>
                        <span className="text-gray-300">•</span>
                        <a
                          className="text-xs text-gray-500 hover:text-blue-600 transition-colors"
                          href={file.url}
                          onClick={(e) => e.stopPropagation()}
                          rel="noopener noreferrer"
                          target="_blank"
                        >
                          Download
                        </a>
                      </>
                    )}
                  </div>
                </div>
                <button
                  aria-label="Remove file"
                  className="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-100 rounded"
                  onClick={() => onRemoveFile(file.uuid)}
                  type="button"
                >
                  <X className="h-4 w-4 text-gray-400 hover:text-red-500 transition-colors" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Loading state */}
      {loading && (
        <div className="flex items-center justify-center py-4">
          <Loader2 className="h-4 w-4 animate-spin text-gray-500 mr-2" />
          <span className="text-sm text-gray-500">Processing files...</span>
        </div>
      )}
    </div>
  );
}
