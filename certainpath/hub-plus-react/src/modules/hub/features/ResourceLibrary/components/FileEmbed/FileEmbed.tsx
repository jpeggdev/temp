import React from "react";
import { FileText } from "lucide-react";
import { getFileNameFromUrl } from "@/modules/hub/features/ResourceLibrary/utils/contentBlockUtils";

interface FileEmbedProps {
  title: string | null;
  contentUrl: string;
  bypassIframeForPdf?: boolean;
}

export function FileEmbed({
  title,
  contentUrl,
  bypassIframeForPdf = false,
}: FileEmbedProps) {
  const fileName = title ?? getFileNameFromUrl(contentUrl.trim());

  if (contentUrl.toLowerCase().endsWith(".pdf") && !bypassIframeForPdf) {
    return (
      <div className="bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden my-6">
        <iframe
          className="w-full border-0"
          height="600"
          src={`https://docs.google.com/viewer?url=${encodeURIComponent(
            contentUrl,
          )}&embedded=true`}
          width="100%"
        />
      </div>
    );
  }

  return (
    <div className="my-6">
      {contentUrl ? (
        <div className="p-4 border rounded-md flex items-center gap-3 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
          <FileText className="w-6 h-6 text-gray-500 dark:text-gray-300" />
          <div className="flex-1">
            <p className="text-sm font-medium break-all">{fileName}</p>
            <p className="text-xs text-gray-500 dark:text-gray-400">
              File download
            </p>
          </div>
          <a
            className="inline-flex items-center justify-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-light transition"
            download
            href={contentUrl}
            rel="noopener noreferrer"
            target="_blank"
          >
            Download
          </a>
        </div>
      ) : (
        <p className="text-gray-500">No file provided.</p>
      )}
    </div>
  );
}
