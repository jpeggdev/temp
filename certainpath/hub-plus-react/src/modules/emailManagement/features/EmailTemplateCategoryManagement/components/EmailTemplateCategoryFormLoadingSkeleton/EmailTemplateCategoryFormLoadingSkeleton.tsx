import React from "react";
import { Skeleton } from "@/components/ui/skeleton";

export default function EmailTemplateCategoryFormLoadingSkeleton() {
  return (
    <div className="space-y-4">
      <div>
        <div className="mb-1 h-4 w-32 bg-gray-200 rounded" />
        <Skeleton className="h-10 w-full bg-gray-200" />
      </div>
      <div>
        <div className="mb-1 h-4 w-40 bg-gray-200 rounded" />
        <Skeleton className="h-10 w-full bg-gray-200" />
      </div>
      <div>
        <div className="mb-1 h-4 w-36 bg-gray-200 rounded" />
        <Skeleton className="h-20 w-full bg-gray-200" />
      </div>
      <div>
        <div className="mb-1 h-4 w-24 bg-gray-200 rounded" />
        <Skeleton className="h-10 w-full bg-gray-200" />
      </div>
      <div className="flex justify-end">
        <Skeleton className="h-10 w-24 bg-gray-200" />
      </div>
    </div>
  );
}
