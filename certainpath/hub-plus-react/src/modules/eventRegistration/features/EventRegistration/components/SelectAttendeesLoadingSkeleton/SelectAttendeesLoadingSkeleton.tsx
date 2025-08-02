import React from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";

function SelectAttendeesLoadingSkeleton() {
  return (
    <MainPageWrapper hideHeader title="Register Attendees">
      <div className="animate-pulse space-y-6">
        <div className="mb-6">
          <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-32 mb-4"></div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-1 space-y-4">
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
          </div>

          <div className="lg:col-span-2 space-y-6">
            <div>
              <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-2"></div>
              <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
            </div>

            <div className="space-y-3">
              <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
              <div className="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>

              <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
              <div className="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>

              <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
              <div className="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>

              <div className="h-20 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
            </div>

            <div>
              <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-2"></div>
              <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
            </div>

            <div className="flex justify-end">
              <div className="h-10 bg-gray-200 dark:bg-gray-700 rounded w-40"></div>
            </div>
          </div>
        </div>
      </div>
    </MainPageWrapper>
  );
}

export default SelectAttendeesLoadingSkeleton;
