import React from "react";
import { Skeleton } from "@/components/ui/skeleton";

export default function EventDetailsSkeleton() {
  return (
    <div>
      <div className="mb-6">
        <Skeleton className="h-6 w-40 bg-gray-200 dark:bg-gray-700" />
      </div>

      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div>
          <div className="flex flex-col md:flex-row gap-6 items-start">
            <Skeleton className="w-full md:w-48 h-48 shrink-0 rounded-lg bg-gray-200 dark:bg-gray-700" />

            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-4 mb-3">
                <Skeleton className="h-8 w-3/4 bg-gray-200 dark:bg-gray-700" />
                <Skeleton className="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700" />
              </div>

              <div className="flex flex-wrap items-center gap-4 text-sm mb-3">
                <Skeleton className="h-6 w-20 rounded-md bg-gray-200 dark:bg-gray-700" />
                <Skeleton className="h-6 w-24 rounded-md bg-gray-200 dark:bg-gray-700" />
                <Skeleton className="h-6 w-16 rounded-md bg-gray-200 dark:bg-gray-700" />
              </div>

              <div className="flex flex-col md:flex-row md:items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
                <Skeleton className="h-4 w-32 bg-gray-200 dark:bg-gray-700" />
              </div>
            </div>
          </div>

          <div className="mt-6 space-y-3">
            <Skeleton className="h-5 w-40 bg-gray-200 dark:bg-gray-700" />
            <Skeleton className="h-4 w-full bg-gray-200 dark:bg-gray-700" />
            <Skeleton className="h-4 w-4/5 bg-gray-200 dark:bg-gray-700" />
            <Skeleton className="h-4 w-3/5 bg-gray-200 dark:bg-gray-700" />
          </div>

          <div className="mt-8">
            <Skeleton className="h-5 w-44 bg-gray-200 dark:bg-gray-700" />
            <div className="space-y-4 mt-3">
              {Array.from({ length: 2 }).map((_, i) => (
                <div
                  className="p-4 border rounded-lg hover:border-primary transition-colors"
                  key={i}
                >
                  <div className="flex items-center justify-between">
                    <div className="space-y-2 text-sm">
                      <Skeleton className="h-4 w-48 bg-gray-200 dark:bg-gray-700" />
                      <Skeleton className="h-4 w-40 bg-gray-200 dark:bg-gray-700" />
                      <Skeleton className="h-5 w-24 rounded-md bg-gray-200 dark:bg-gray-700" />
                    </div>
                    <Skeleton className="h-8 w-24 rounded-md bg-gray-200 dark:bg-gray-700" />
                  </div>
                  <div className="mt-2 space-y-1">
                    <Skeleton className="h-4 w-full bg-gray-200 dark:bg-gray-700" />
                    <Skeleton className="h-4 w-2/3 bg-gray-200 dark:bg-gray-700" />
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="mt-8">
            <Skeleton className="h-5 w-40 bg-gray-200 dark:bg-gray-700" />
            <div className="space-y-2 mt-3">
              {Array.from({ length: 2 }).map((_, i) => (
                <div
                  className="p-3 border rounded-lg flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                  key={i}
                >
                  <Skeleton className="h-5 w-1/2 bg-gray-200 dark:bg-gray-700" />
                  <Skeleton className="h-4 w-16 bg-gray-200 dark:bg-gray-700" />
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
