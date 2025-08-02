import React from "react";
import { Skeleton } from "@/components/ui/skeleton";

export default function ResourceDetailsSkeleton() {
  return (
    <div className="space-y-6">
      <div className="flex items-center gap-2 px-6">
        <Skeleton className="h-10 w-32 bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <div className="p-6">
              <div className="flex flex-col md:flex-row gap-6 items-start">
                <Skeleton className="w-full md:w-48 h-48 shrink-0 rounded-lg bg-gray-200 dark:bg-gray-700" />

                <div className="flex-1 min-w-0">
                  <div className="flex flex-wrap items-center justify-between gap-4 mb-3">
                    <Skeleton className="h-10 w-3/4 bg-gray-200 dark:bg-gray-700" />
                    <div className="flex gap-2">
                      <Skeleton className="h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700" />
                      <Skeleton className="h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700" />
                    </div>
                  </div>
                  <Skeleton className="h-6 w-full mb-3 bg-gray-200 dark:bg-gray-700" />
                  <div className="flex gap-4">
                    <Skeleton className="h-6 w-24 bg-gray-200 dark:bg-gray-700" />
                    <Skeleton className="h-6 w-24 bg-gray-200 dark:bg-gray-700" />
                    <Skeleton className="h-6 w-32 bg-gray-200 dark:bg-gray-700" />
                  </div>
                </div>
              </div>

              <div className="space-y-2 mt-6">
                <Skeleton className="h-4 w-full bg-gray-200 dark:bg-gray-700" />
                <Skeleton className="h-4 w-full bg-gray-200 dark:bg-gray-700" />
                <Skeleton className="h-4 w-3/4 bg-gray-200 dark:bg-gray-700" />
              </div>

              <div className="space-y-4 pt-6">
                <Skeleton className="h-[300px] w-full rounded-lg bg-gray-200 dark:bg-gray-700" />
                <div className="space-y-2">
                  <Skeleton className="h-4 w-full bg-gray-200 dark:bg-gray-700" />
                  <Skeleton className="h-4 w-full bg-gray-200 dark:bg-gray-700" />
                  <Skeleton className="h-4 w-2/3 bg-gray-200 dark:bg-gray-700" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <Skeleton className="h-6 w-1/2 mb-4 bg-gray-200 dark:bg-gray-700" />
            <div className="space-y-4">
              <div className="space-y-2">
                <Skeleton className="h-4 w-24 bg-gray-200 dark:bg-gray-700" />
                <div className="flex flex-wrap gap-2">
                  <Skeleton className="h-6 w-16 rounded-full bg-gray-200 dark:bg-gray-700" />
                  <Skeleton className="h-6 w-20 rounded-full bg-gray-200 dark:bg-gray-700" />
                  <Skeleton className="h-6 w-14 rounded-full bg-gray-200 dark:bg-gray-700" />
                </div>
              </div>
              <Skeleton className="h-px w-full bg-gray-200 dark:bg-gray-700" />
              <div className="space-y-2">
                <Skeleton className="h-4 w-24 bg-gray-200 dark:bg-gray-700" />
                <div className="space-y-1">
                  <div className="flex justify-between">
                    <Skeleton className="h-4 w-20 bg-gray-200 dark:bg-gray-700" />
                    <Skeleton className="h-4 w-24 bg-gray-200 dark:bg-gray-700" />
                  </div>
                  <div className="flex justify-between">
                    <Skeleton className="h-4 w-20 bg-gray-200 dark:bg-gray-700" />
                    <Skeleton className="h-4 w-24 bg-gray-200 dark:bg-gray-700" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <Skeleton className="h-6 w-2/3 mb-4 bg-gray-200 dark:bg-gray-700" />
            <div className="space-y-2">
              <Skeleton className="h-4 w-full bg-gray-200 dark:bg-gray-700" />
              <Skeleton className="h-4 w-2/3 bg-gray-200 dark:bg-gray-700" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
