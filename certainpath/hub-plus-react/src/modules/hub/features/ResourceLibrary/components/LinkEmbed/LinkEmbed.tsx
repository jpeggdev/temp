import React from "react";
import { FileText } from "lucide-react";

interface LinkEmbedProps {
  title: string | null;
  contentUrl: string;
}

export function LinkEmbed({ title, contentUrl }: LinkEmbedProps) {
  return (
    <div className="my-6">
      {contentUrl ? (
        <div className="p-4 border rounded-md flex items-center gap-3 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
          <FileText className="w-6 h-6 text-gray-500 dark:text-gray-300" />
          <div className="flex-1">
            <p className="text-sm font-medium break-all">{title}</p>
          </div>
          <a
            className="inline-flex items-center justify-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-light transition"
            download
            href={contentUrl}
            rel="noopener noreferrer"
            target="_blank"
          >
            View
          </a>
        </div>
      ) : (
        <p className="text-gray-500">No link provided.</p>
      )}
    </div>
  );
}
