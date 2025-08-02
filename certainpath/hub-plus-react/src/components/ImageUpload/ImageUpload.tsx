import { useState, useCallback, useRef } from "react";
import { Image as LucideImage, Loader2, X } from "lucide-react";
import { cn } from "@/components/ui/lib/utils";

interface UploadResponse {
  path: string;
  url: string;
  fileId?: number;
}

interface ImageUploadProps {
  onUpload: (file: File) => Promise<UploadResponse>;
  value?: string;
  onChange?: (value: string | { fileUrl: string; fileId: number }) => void;
  onPreviewChange?: (preview: string) => void;
  className?: string;
  label: string; // e.g. "Upload thumbnail"
  width?: number;
  height?: number;
}

const FILE_CONSTRAINTS = {
  maxSize: 5 * 1024 * 1024,
  allowedTypes: ["image/jpeg", "image/png"] as const,
} as const;

interface ImageDimensions {
  targetWidth: number;
  targetHeight: number;
  sourceX: number;
  sourceY: number;
  sourceWidth: number;
  sourceHeight: number;
}

export function ImageUpload({
  onUpload,
  value,
  onChange,
  onPreviewChange,
  className,
  label,
}: ImageUploadProps): JSX.Element {
  const [isDragging, setIsDragging] = useState<boolean>(false);
  const [isUploading, setIsUploading] = useState<boolean>(false);
  const [uploadProgress, setUploadProgress] = useState<number>(0);
  const [preview, setPreview] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const fileInputRef = useRef<HTMLInputElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);

  const validateFile = (file: File): string | null => {
    if (!file.type.startsWith("image/")) {
      return "Please upload an image file.";
    }
    if (
      !FILE_CONSTRAINTS.allowedTypes.includes(
        file.type as (typeof FILE_CONSTRAINTS.allowedTypes)[number],
      )
    ) {
      return "Please upload a JPEG or PNG file.";
    }
    if (file.size > FILE_CONSTRAINTS.maxSize) {
      return "File size must be less than 5MB.";
    }
    return null;
  };

  const calculateDimensions = (image: HTMLImageElement): ImageDimensions => {
    const aspectRatio = 1;
    const imageRatio = image.width / image.height;

    if (imageRatio > aspectRatio) {
      const sourceHeight = image.height;
      const sourceWidth = image.height * aspectRatio;
      return {
        targetWidth: 400,
        targetHeight: 400,
        sourceWidth,
        sourceHeight,
        sourceX: (image.width - sourceWidth) / 2,
        sourceY: 0,
      };
    } else {
      const sourceWidth = image.width;
      const sourceHeight = image.width / aspectRatio;
      return {
        targetWidth: 400,
        targetHeight: 400,
        sourceWidth,
        sourceHeight,
        sourceX: 0,
        sourceY: (image.height - sourceHeight) / 2,
      };
    }
  };

  const cropImage = async (
    image: HTMLImageElement,
    mimeType: string,
  ): Promise<Blob> => {
    return new Promise((resolve, reject) => {
      const canvas = canvasRef.current;
      if (!canvas) {
        return reject("No canvas found");
      }

      const dimensions = calculateDimensions(image);
      canvas.width = dimensions.targetWidth;
      canvas.height = dimensions.targetHeight;

      const ctx = canvas.getContext("2d");
      if (!ctx) {
        return reject("No 2D context");
      }

      ctx.drawImage(
        image,
        dimensions.sourceX,
        dimensions.sourceY,
        dimensions.sourceWidth,
        dimensions.sourceHeight,
        0,
        0,
        dimensions.targetWidth,
        dimensions.targetHeight,
      );

      const isPng = mimeType === "image/png";
      const outputType = isPng ? "image/png" : "image/jpeg";
      const quality = isPng ? undefined : 0.95;

      canvas.toBlob(
        (blob) => {
          if (blob) resolve(blob);
          else reject("Failed to convert canvas to blob");
        },
        outputType,
        quality,
      );
    });
  };

  const handleFileChange = useCallback(
    async (file: File) => {
      if (!file) {
        console.log("No file selected");
        return;
      }

      console.log("File selected:", {
        name: file.name,
        type: file.type,
        size: file.size,
      });

      const validationError = validateFile(file);
      if (validationError) {
        console.error("File validation failed:", validationError);
        setError(validationError);
        return;
      }

      try {
        setError(null);
        setIsUploading(true);
        setUploadProgress(0);

        const image = new Image();
        const imageUrl = URL.createObjectURL(file);

        image.onload = async () => {
          const croppedBlob = await cropImage(image, file.type);
          const isPng = file.type === "image/png";
          const extension = isPng ? ".png" : ".jpg";
          const newFileName = file.name.replace(/\.\w+$/, "") + extension;

          const fileToUpload = new File([croppedBlob], newFileName, {
            type: file.type,
          });

          const previewUrl = URL.createObjectURL(fileToUpload);
          setPreview(previewUrl);
          onPreviewChange?.(previewUrl);

          const progressInterval = setInterval(() => {
            setUploadProgress((prev) => Math.min(prev + 10, 90));
          }, 100);

          console.log("Uploading file...");
          const { path, url, fileId } = await onUpload(fileToUpload);
          console.log("Upload successful:", { path, url, fileId });

          clearInterval(progressInterval);
          setUploadProgress(100);

          if (onChange) {
            if (typeof fileId === "number") {
              onChange({ fileUrl: url, fileId });
            } else {
              onChange(url);
            }
          }

          setIsUploading(false);
          setUploadProgress(0);
          URL.revokeObjectURL(imageUrl);
        };

        image.src = imageUrl;
      } catch (err) {
        console.error("Upload failed:", err);
        setError("Failed to upload image. Please try again.");
        setIsUploading(false);
        setUploadProgress(0);
      }
    },
    [onUpload, onChange, onPreviewChange],
  );

  const handleDrop = useCallback(
    (e: React.DragEvent<HTMLDivElement>) => {
      e.preventDefault();
      setIsDragging(false);
      const file = e.dataTransfer.files[0];
      if (file) handleFileChange(file);
    },
    [handleFileChange],
  );

  const handleDragOver = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(true);
  }, []);

  const handleDragLeave = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(false);
  }, []);

  const handleClick = useCallback(() => {
    fileInputRef.current?.click();
  }, []);

  const handleInputChange = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      const selectedFile = e.target.files?.[0];
      if (selectedFile) handleFileChange(selectedFile);
    },
    [handleFileChange],
  );

  const handleRemove = useCallback(
    (e: React.MouseEvent) => {
      e.stopPropagation();
      setPreview(null);
      onChange?.("");
      onPreviewChange?.("");
      if (fileInputRef.current) {
        fileInputRef.current.value = "";
      }
    },
    [onChange, onPreviewChange],
  );

  return (
    <div>
      <div className="mb-4 flex items-center flex-wrap gap-4">
        {(preview || value) && (
          <div className="relative w-[200px] h-[200px]">
            <img
              alt="Upload"
              className="object-cover rounded-lg"
              src={preview || value}
            />
            <button
              className="absolute right-2 top-2 rounded-full bg-gray-900/50 p-1 text-white hover:bg-gray-900/75"
              onClick={handleRemove}
              type="button"
            >
              <X className="h-4 w-4" />
            </button>
          </div>
        )}
        <div
          className={cn(
            "relative w-[200px] h-[200px] flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-gray-900/25 transition-colors",
            {
              "border-blue-500 bg-blue-50": isDragging,
              "hover:bg-gray-50": !isDragging && !isUploading && !preview,
            },
            className,
          )}
          onClick={handleClick}
          onDragLeave={handleDragLeave}
          onDragOver={handleDragOver}
          onDrop={handleDrop}
        >
          <input
            accept={FILE_CONSTRAINTS.allowedTypes.join(",")}
            className="hidden"
            onChange={handleInputChange}
            ref={fileInputRef}
            type="file"
          />

          {isUploading ? (
            <div className="flex flex-col items-center justify-center p-4">
              <Loader2 className="h-8 w-8 animate-spin text-gray-400" />
              <p className="mt-2 text-sm text-gray-500">
                Uploading... {uploadProgress}%
              </p>
            </div>
          ) : (
            <div className="flex flex-col items-center justify-center p-4">
              <LucideImage className="mx-auto h-12 w-12 text-gray-300" />
              <div className="mt-4 flex text-sm leading-6 text-gray-600">
                <span className="relative cursor-pointer rounded-md font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500">
                  {label}
                </span>
                <p className="pl-1">or drag and drop</p>
              </div>
              <p className="text-xs leading-5 text-gray-600">
                JPEG or PNG up to 5MB
              </p>
            </div>
          )}
        </div>
      </div>
      {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
      <canvas className="hidden" ref={canvasRef} />
    </div>
  );
}
