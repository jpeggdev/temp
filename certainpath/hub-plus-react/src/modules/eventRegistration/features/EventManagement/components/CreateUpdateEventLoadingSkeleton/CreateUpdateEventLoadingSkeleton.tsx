import React from "react";
import { Skeleton } from "@/components/ui/skeleton";

export default function CreateUpdateEventLoadingSkeleton() {
  return (
    <div className="bg-white p-4 space-y-6 rounded-md shadow-sm">
      <div className="h-6 w-1/2 bg-gray-200 dark:bg-gray-700 rounded" />
      <div className="h-4 w-2/3 bg-gray-200 dark:bg-gray-700 rounded" />

      <div className="space-y-4">
        <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="space-y-4">
        <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="space-y-4">
        <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-24 w-full bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="space-y-4">
        <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-56 bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="space-y-4 grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
        </div>
        <div className="space-y-2">
          <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
        </div>
      </div>

      <div className="p-4 border rounded-md space-y-2">
        <Skeleton className="h-4 w-20 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-4 w-64 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-6 w-12 mt-2 bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div className="space-y-2">
          <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
        </div>
        <div className="space-y-2">
          <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div className="space-y-2">
          <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
        </div>
        <div className="space-y-2">
          <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
          <Skeleton className="h-10 w-full bg-gray-200 dark:bg-gray-700" />
        </div>
      </div>

      <div className="space-y-2">
        <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-6 w-full bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-6 w-full bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-6 w-3/4 bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="flex justify-end gap-4 mt-6">
        <Skeleton className="h-10 w-24 bg-gray-200 dark:bg-gray-700" />
        <Skeleton className="h-10 w-20 bg-gray-200 dark:bg-gray-700" />
      </div>
    </div>
  );
}
