import React from "react";
import { Skeleton } from "@/components/ui/skeleton";

function EventSessionFormLoadingSkeleton() {
  return (
    <div className="space-y-4">
      <div>
        <div className="mb-1 h-4 w-32 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div>
        <div className="mb-1 h-4 w-40 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div>
        <div className="mb-1 h-4 w-36 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div>
        <div className="mb-1 h-4 w-20 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="flex flex-col space-y-1">
        <div className="mb-1 h-4 w-24 rounded bg-gray-200 dark:bg-gray-700" />
        <div className="flex items-center space-x-2">
          <Skeleton className="h-5 w-5 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-4 w-24 bg-gray-200 dark:bg-gray-700" />
        </div>
      </div>

      <div>
        <div className="mb-1 h-4 w-16 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div>
        <div className="mb-1 h-4 w-24 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div>
        <div className="mb-1 h-4 w-36 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div>
        <div className="mb-1 h-4 w-24 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div>
        <div className="mb-1 h-4 w-14 rounded bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-24 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="flex flex-col space-y-1">
        <div className="mb-1 h-4 w-24 rounded bg-gray-200 dark:bg-gray-700" />
        <div className="flex items-center space-x-2">
          <Skeleton className="h-5 w-5 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-4 w-24 bg-gray-200 dark:bg-gray-700" />
        </div>
      </div>

      <div className="flex justify-end">
        <Skeleton className="h-10 w-24 bg-gray-200 dark:bg-gray-700" />
      </div>
    </div>
  );
}

export default EventSessionFormLoadingSkeleton;
