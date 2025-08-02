import React from "react";
import { Button } from "@/components/ui/button";
import {
  Loader2,
  Image as ImageIcon,
  Trash2,
  RefreshCw,
  Upload,
} from "lucide-react";

interface ThumbnailDisplayProps {
  thumbnailUrl?: string | null;
  thumbnailFileName?: string;
  loading?: boolean;
  onSelect: () => void;
  onRemove: () => void;
}

export default function ThumbnailDisplay({
  thumbnailUrl,
  thumbnailFileName,
  loading,
  onSelect,
  onRemove,
}: ThumbnailDisplayProps) {
  if (loading) {
    return (
      <div className="flex items-center justify-center p-6 border border-dashed rounded-md">
        <Loader2 className="h-6 w-6 animate-spin text-gray-500 mr-2" />
        <span className="text-gray-500">Loading image...</span>
      </div>
    );
  }

  if (thumbnailUrl) {
    return (
      <div className="relative group max-w-md">
        <div className="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
          <div className="relative">
            <img
              alt="Thumbnail"
              className="w-full h-64 object-cover"
              src={thumbnailUrl}
            />
            <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200" />
          </div>
          <div className="p-4 bg-white border-t border-gray-200">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2 text-sm text-gray-600">
                <ImageIcon className="text-blue-500" size={16} />
                <span className="truncate max-w-[200px]">
                  {thumbnailFileName || "Event thumbnail"}
                </span>
              </div>
              <div className="flex items-center gap-1">
                <Button
                  className="opacity-0 group-hover:opacity-100 transition-opacity"
                  onClick={onSelect}
                  size="sm"
                  type="button"
                  variant="ghost"
                >
                  <RefreshCw className="mr-1" size={14} />
                  Replace
                </Button>
                <Button
                  className="opacity-0 group-hover:opacity-100 transition-opacity"
                  onClick={onRemove}
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
    );
  }

  return (
    <div className="max-w-md">
      <button
        className="w-full border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 hover:bg-gray-50 transition-all cursor-pointer group"
        onClick={onSelect}
        type="button"
      >
        <Upload className="mx-auto h-10 w-10 text-gray-400 group-hover:text-gray-500 transition-colors mb-3" />
        <p className="text-sm font-medium text-gray-700 mb-1">
          Click to upload thumbnail
        </p>
        <p className="text-xs text-gray-500">or browse from File Manager</p>
      </button>
    </div>
  );
}
