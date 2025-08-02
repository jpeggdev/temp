"use client";

import React, { useState, useRef } from "react";

interface ChartNavigationSliderProps {
  totalPages: number;
  currentPage: number;
  onPageChange: (page: number) => void;
  postalCodes: string[];
  itemsPerPage: number;
  theme: "light" | "dark";
}

export function ChartNavigationSlider({
  totalPages,
  currentPage,
  onPageChange,
  postalCodes,
  itemsPerPage,
  theme,
}: ChartNavigationSliderProps) {
  const [isDragging, setIsDragging] = useState(false);
  const [hoveredSegment, setHoveredSegment] = useState<number | null>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  const segmentWidth = 100 / totalPages;
  const leftPercent = currentPage * segmentWidth;

  const getZipRange = (page: number) => {
    const start = page * itemsPerPage;
    const zips = postalCodes.slice(start, start + itemsPerPage);
    return zips.length ? `${zips[0]} – ${zips[zips.length - 1]}` : "No data";
  };

  const handleMouseDown = (e: React.MouseEvent) => {
    setIsDragging(true);
    handleMouseMove(e);
  };

  const handleMouseUp = () => setIsDragging(false);
  const handleMouseLeave = () => {
    setIsDragging(false);
    setHoveredSegment(null);
  };

  const handleMouseMove = (e: React.MouseEvent) => {
    if (!isDragging || !containerRef.current) return;
    const rect = containerRef.current.getBoundingClientRect();
    const x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
    const newPage = Math.floor((x / rect.width) * totalPages);
    onPageChange(newPage);
  };

  const isDark = theme === "dark";
  const tooltipBg = isDark ? "bg-gray-800" : "bg-white";
  const tooltipText = isDark ? "text-gray-100" : "text-gray-900";
  const tooltipBorder = isDark ? "border-gray-600" : "border-gray-200";

  return (
    <div
      className={`h-10 border rounded-lg relative cursor-pointer select-none transition-all ${
        isDragging ? "bg-gray-200 shadow-inner" : "bg-gray-50 hover:bg-gray-200"
      }`}
      onMouseDown={handleMouseDown}
      onMouseLeave={handleMouseLeave}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      ref={containerRef}
    >
      <div className="absolute inset-0 flex">
        {Array.from({ length: totalPages }).map((_, i) => (
          <div
            className="flex-1 border-r border-gray-300 last:border-r-0 relative hover:bg-gray-200 transition-colors"
            key={i}
            onClick={() => onPageChange(i)}
            onKeyDown={(e) => {
              if (e.key === "Enter" || e.key === " ") onPageChange(i);
            }}
            onMouseEnter={() => setHoveredSegment(i)}
            onMouseLeave={() => setHoveredSegment(null)}
            role="button"
            tabIndex={0}
          >
            {hoveredSegment === i && (
              <div className="absolute top-full mt-2 z-10 left-1/2 -translate-x-1/2">
                <div
                  className={`min-w-[10rem] whitespace-nowrap text-sm p-3 rounded border shadow-md ${tooltipBg} ${tooltipText} ${tooltipBorder}`}
                >
                  <div className="font-semibold mb-1">
                    View {i + 1} of {totalPages}
                  </div>
                  <div>ZIPs: {getZipRange(i)}</div>
                </div>
              </div>
            )}
          </div>
        ))}
      </div>

      <div
        className={`h-full bg-blue-200 border-2 border-blue-500 rounded absolute flex items-center justify-center transition-all duration-150 ${
          isDragging ? "bg-blue-300 border-blue-600 shadow-lg" : ""
        }`}
        style={{
          left: `${leftPercent}%`,
          width: `${segmentWidth}%`,
          pointerEvents: "none",
        }}
      >
        <div className="w-1 h-6 bg-blue-600 rounded-full opacity-70" />
      </div>

      {!isDragging && (
        <div className="absolute inset-0 flex items-center justify-center text-xs text-gray-600 pointer-events-none select-none">
          Click and drag to navigate
        </div>
      )}
    </div>
  );
}
