import React, { useState } from "react";
import InfiniteScroll from "react-infinite-scroll-component";

import { ResourceCard } from "@/modules/hub/features/ResourceLibrary/components/ResourceCard/ResourceCard";
import { FilterSidebar } from "@/modules/hub/features/ResourceLibrary/components/FilterSidebar/FilterSidebar/FilterSidebar";
import ActiveFiltersHandler from "@/modules/hub/features/ResourceLibrary/components/ActiveFiltersHandler/ActiveFiltersHandler";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import "./ResourceLibrary.css";
import { useResourcesLibrary } from "../../hooks/useResourceLibrary";
import { useIsMobile } from "@/hooks/use-mobile";
import MobileFilterToggleButton from "../MobileFilterToggleButton/MobileFilterToggleButton";
import { ResourcesLibrarySkeleton } from "@/modules/hub/features/ResourceLibrary/components/ResourceLibrary/ResourceLibrarySkeleton";

export function ResourcesLibrary() {
  const {
    form,
    resources,
    hasMore,
    loading,
    isInitialLoading,
    sidebarMetadata,
    isMetadataLoading,
    fetchMore,
    handleClearFilters,
  } = useResourcesLibrary();

  const isMobile = useIsMobile();
  const [isFilterSidebarOpen, setIsFilterSidebarOpen] = useState(false);

  if (isInitialLoading || (loading && resources.length === 0)) {
    return <ResourcesLibrarySkeleton />;
  }

  return (
    <MainPageWrapper
      actions={
        <MobileFilterToggleButton
          onClick={() => setIsFilterSidebarOpen(true)}
        />
      }
      subtitle="Access and discover learning materials, documents, videos, and more"
      title="Resource Library"
    >
      <div className="flex flex-col md:flex-row gap-6">
        {/* Sidebar */}
        <div
          className={
            isMobile
              ? undefined
              : "w-80 shrink-0 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 p-2 sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto"
          }
        >
          <FilterSidebar
            filters={sidebarMetadata}
            form={form}
            isLoading={isMetadataLoading}
            isOpen={isFilterSidebarOpen}
            onOpenChange={setIsFilterSidebarOpen}
          />
        </div>

        <div className="flex-1 min-w-0">
          <ActiveFiltersHandler form={form} onClearAll={handleClearFilters} />

          <InfiniteScroll
            dataLength={resources.length}
            endMessage={
              <div className="text-center py-4">
                <p className="text-gray-500 dark:text-gray-400">
                  No more resources to load
                </p>
              </div>
            }
            hasMore={hasMore}
            loader={
              <div className="text-center py-4">
                <p className="text-gray-500 dark:text-gray-400">
                  Loading more...
                </p>
              </div>
            }
            next={fetchMore}
            style={{ overflow: "visible" }}
          >
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {resources.length > 0 ? (
                resources.map((resource) => (
                  <div className="fade-in" key={resource.id}>
                    <ResourceCard resource={resource} />
                  </div>
                ))
              ) : (
                <div className="col-span-full text-center py-12">
                  <p className="text-gray-500 dark:text-gray-400 text-lg">
                    No resources found matching your criteria.
                  </p>
                  <button
                    className="mt-4 text-blue-600 dark:text-blue-400 hover:underline"
                    onClick={handleClearFilters}
                  >
                    Clear all filters
                  </button>
                </div>
              )}
            </div>
          </InfiniteScroll>
        </div>
      </div>
    </MainPageWrapper>
  );
}
