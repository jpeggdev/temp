import { ReactNode, useRef, useState } from "react";
import { Upload } from "lucide-react";

interface DraggableFileWrapperProps {
  children: ReactNode;
  onFilesDropped: (files: File[]) => void;
  className?: string;
}

export const DraggableFileWrapper = ({
  children,
  onFilesDropped,
  className = "",
}: DraggableFileWrapperProps) => {
  const [isDragging, setIsDragging] = useState(false);
  const dragCounterRef = useRef(0);

  const handleDragEnter = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    dragCounterRef.current++;
    if (e.dataTransfer.items && e.dataTransfer.items.length > 0) {
      setIsDragging(true);
    }
  };

  const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    dragCounterRef.current--;
    if (dragCounterRef.current === 0) {
      setIsDragging(false);
    }
  };

  const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
  };

  const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
    dragCounterRef.current = 0;
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      const files = Array.from(e.dataTransfer.files);
      onFilesDropped(files);
      e.dataTransfer.clearData();
    }
  };

  return (
    <div
      className={`file-manager-container ${isDragging ? "dragging" : ""} ${className}`}
      onDragEnter={handleDragEnter}
      onDragLeave={handleDragLeave}
      onDragOver={handleDragOver}
      onDrop={handleDrop}
    >
      {/* Drag overlay */}
      {isDragging && (
        <div className="drag-overlay">
          <div className="drag-overlay-content">
            <Upload className="mb-4" size={48} />
            <h3 className="text-lg font-medium mb-2">Drop files to upload</h3>
            <p className="text-sm text-gray-500 dark:text-gray-400">
              Files will be uploaded to the current folder
            </p>
          </div>
        </div>
      )}
      {children}
    </div>
  );
};
