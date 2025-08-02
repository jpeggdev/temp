import React, { useState, useEffect } from "react";
import { useSubscription } from "@apollo/client";
import { ON_COMPANY_DATA_IMPORT_JOB_LOG_STREAM_SUBSCRIPTION } from "@/modules/stochastic/features/ImportStatus/graphql/subscriptions/onCompanyDataImportJobLogStreamSubscriptions/onCompanyDataImportJobLogStreamSubscriptions";
import { CompanyDataImportLogStreamSubscriptionData } from "@/modules/stochastic/features/ImportStatus/graphql/subscriptions/onCompanyDataImportJobLogStreamSubscriptions/types";

interface LogStreamModalProps {
  jobId: number;
  onClose: () => void;
}

const LogStreamModal: React.FC<LogStreamModalProps> = ({ jobId, onClose }) => {
  // Local state that we append to whenever new data arrives
  const [combinedLogs, setCombinedLogs] = useState("");

  // GraphQL subscription
  const { data, loading, error } =
    useSubscription<CompanyDataImportLogStreamSubscriptionData>(
      ON_COMPANY_DATA_IMPORT_JOB_LOG_STREAM_SUBSCRIPTION,
      {
        variables: { jobId },
      },
    );

  // Each time we get new subscription data, append it to "combinedLogs"
  useEffect(() => {
    if (!loading && data) {
      const newChunk = data.company_data_import_job?.[0]?.log_stream ?? "";
      // Append new logs to existing state
      if (newChunk) {
        setCombinedLogs((prev) => prev + newChunk);
      }
    }
  }, [data, loading]);

  return (
    <div className="fixed inset-0 flex items-center justify-center z-50">
      {/* Overlay */}
      <div className="absolute inset-0 bg-black opacity-50" onClick={onClose} />

      {/* Modal Container */}
      <div
        className="relative bg-white p-6 rounded shadow-lg z-10
                      max-w-3xl max-h-[80vh] overflow-y-auto
                      w-full min-w-[500px]"
      >
        <h2 className="text-xl font-bold mb-4">Log Stream</h2>

        {/* Show loading/error or the log content */}
        {loading && <p className="text-sm text-gray-600">Loading log...</p>}
        {error && (
          <p className="text-sm text-red-600">
            Error fetching log: {error.message}
          </p>
        )}
        {!loading && !error && (
          <pre className="whitespace-pre-wrap break-words text-sm bg-light p-4 rounded">
            {combinedLogs || "(No logs yet)"}
          </pre>
        )}

        <div className="mt-4 flex space-x-2">
          <button
            className="px-3 py-1 rounded bg-light hover:bg-dark text-fontColor text-sm"
            onClick={() => {
              navigator.clipboard.writeText(combinedLogs);
              alert("Log stream copied to clipboard!");
            }}
          >
            Copy Log
          </button>
          <button
            className="px-3 py-1 rounded bg-secondary hover:bg-secondary-dark text-white text-sm"
            onClick={onClose}
          >
            Close
          </button>
        </div>
      </div>
    </div>
  );
};

export default LogStreamModal;
