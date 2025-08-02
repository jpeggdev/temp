import React from "react";

export const FilterSidebarSkeleton: React.FC = () => {
  const SkeletonBlock = ({
    width = "w-32",
    height = "h-5",
    className = "",
  }) => (
    <div
      className={`bg-gray-300 rounded ${width} ${height} animate-pulse ${className}`}
    />
  );

  const SkeletonToggle = () => (
    <div className="flex items-center space-x-2">
      <div className="w-10 h-5 bg-gray-300 rounded-full animate-pulse" />
      <div className="h-5 bg-gray-300 rounded w-24 animate-pulse" />
    </div>
  );

  const SkeletonPill = () => (
    <div className="h-8 rounded-full bg-gray-300 animate-pulse px-4" />
  );

  const SkeletonCheckbox = () => (
    <div className="flex items-center space-x-2">
      <div className="w-4 h-4 bg-gray-300 rounded animate-pulse" />
      <div className="h-4 bg-gray-300 rounded w-24 animate-pulse" />
    </div>
  );

  return (
    <div className="p-4 max-w-xs w-full space-y-5">
      {/* Title + Subtitle */}
      <div className="space-y-1">
        <SkeletonBlock height="h-6" width="w-24" />
        <SkeletonBlock height="h-4" width="w-40" />
      </div>

      {/* Search */}
      <SkeletonBlock height="h-10" width="w-full" />

      {/* Favorite toggle */}
      <div className="space-y-2">
        <SkeletonBlock height="h-5" width="w-28" />
        <SkeletonToggle />
      </div>

      {/* Content Types */}
      <div className="space-y-2">
        <SkeletonBlock height="h-5" width="w-32" />
        <div className="space-y-2">
          {Array(3)
            .fill(0)
            .map((_, i) => (
              <SkeletonPill key={i} />
            ))}
        </div>
      </div>

      {/* Trades */}
      <div className="space-y-2">
        <SkeletonBlock height="h-5" width="w-20" />
        <div className="space-y-2">
          {Array(4)
            .fill(0)
            .map((_, i) => (
              <SkeletonPill key={i} />
            ))}
        </div>
      </div>

      {/* Job Titles */}
      <div className="space-y-2">
        <SkeletonBlock height="h-5" width="w-20" />
        <div className="space-y-2">
          {Array(5)
            .fill(0)
            .map((_, i) => (
              <SkeletonCheckbox key={i} />
            ))}
        </div>
      </div>

      {/* Categories */}
      <div className="space-y-2">
        <SkeletonBlock height="h-5" width="w-24" />
        <div className="space-y-2">
          {Array(3)
            .fill(0)
            .map((_, i) => (
              <SkeletonCheckbox key={i} />
            ))}
        </div>
        <SkeletonBlock height="h-4" width="w-20" /> {/* View more */}
      </div>
    </div>
  );
};
