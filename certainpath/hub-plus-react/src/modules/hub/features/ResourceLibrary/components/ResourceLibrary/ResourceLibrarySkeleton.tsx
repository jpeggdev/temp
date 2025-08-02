import React from "react";
import { ResourceCardsSkeleton } from "@/modules/hub/features/ResourceLibrary/components/ResourceCardsSkeleton/ResourceCardsSkeleton";
import { FilterSidebarSkeleton } from "@/modules/hub/features/ResourceLibrary/components/FilterSidebar/FilterSidebar/FilterSidebarSkeleton";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import MobileFilterToggleButton from "../MobileFilterToggleButton/MobileFilterToggleButton";
import { useIsMobile } from "@/hooks/use-mobile";

export function ResourcesLibrarySkeleton() {
  const isMobile = useIsMobile();

  return (
    <MainPageWrapper
      actions={<MobileFilterToggleButton onClick={() => {}} />}
      subtitle="Access and discover learning materials, documents, videos, and more"
      title="Resource Library"
    >
      {isMobile ? (
        <ResourceCardsSkeleton count={8} />
      ) : (
        <div className="flex flex-col md:flex-row gap-6">
          <div className="w-80 shrink-0 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 overflow-y-auto p-2">
            <FilterSidebarSkeleton />
          </div>

          <div className="flex-1 min-w-0">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <ResourceCardsSkeleton count={8} />
            </div>
          </div>
        </div>
      )}
    </MainPageWrapper>
  );
}
