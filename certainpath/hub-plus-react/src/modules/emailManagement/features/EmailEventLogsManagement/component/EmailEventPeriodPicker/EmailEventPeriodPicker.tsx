import React, { useRef, useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { emailEventPeriodFilter } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogMetadata/types";

interface EmailEventPeriodPickerProps {
  selectedEmailEventPeriod: emailEventPeriodFilter;
  setEmailEventPeriod: (period: emailEventPeriodFilter) => void;
}

export default function EmailEventPeriodPicker({
  selectedEmailEventPeriod,
  setEmailEventPeriod,
}: EmailEventPeriodPickerProps) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const firstItemRef = useRef<HTMLButtonElement>(null);
  const lastItemRef = useRef<HTMLButtonElement>(null);

  const [showLeftFade, setShowLeftFade] = useState(false);
  const [showRightFade, setShowRightFade] = useState(false);

  useEffect(() => {
    const observerOptions = {
      root: scrollRef.current,
      threshold: 1.0,
    };

    const firstObserver = new IntersectionObserver(([entry]) => {
      setShowLeftFade(!entry.isIntersecting);
    }, observerOptions);

    const lastObserver = new IntersectionObserver(([entry]) => {
      setShowRightFade(!entry.isIntersecting);
    }, observerOptions);

    const firstEl = firstItemRef.current;
    const lastEl = lastItemRef.current;

    if (firstEl) firstObserver.observe(firstEl);
    if (lastEl) lastObserver.observe(lastEl);

    return () => {
      if (firstEl) firstObserver.unobserve(firstEl);
      if (lastEl) lastObserver.unobserve(lastEl);
    };
  }, []);

  return (
    <div className="relative w-full pb-1">
      {/* Left scroll fade */}
      {showLeftFade && (
        <div className="pointer-events-none absolute left-0 top-0 h-full w-10 bg-gradient-to-r from-white/90 to-transparent z-10 transition-opacity duration-300 rounded-l-lg" />
      )}

      {/* Right scroll fade */}
      {showRightFade && (
        <div className="pointer-events-none absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-white/90 to-transparent z-10 transition-opacity duration-300 rounded-r-lg" />
      )}

      <div
        className="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent"
        ref={scrollRef}
      >
        <div className="flex space-x-2 bg-gray-100 p-1 rounded-lg w-max mb-1">
          <Button
            className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap
              ${
                selectedEmailEventPeriod === "today"
                  ? "bg-white shadow text-black hover:bg-gray-50"
                  : "text-gray-500 hover:bg-gray-200"
              }`}
            onClick={() => setEmailEventPeriod("today")}
            ref={firstItemRef}
            type="button"
            variant="ghost"
          >
            Today
          </Button>

          <Button
            className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap
              ${
                selectedEmailEventPeriod === "last_7_days"
                  ? "bg-white shadow text-black hover:bg-gray-50"
                  : "text-gray-500 hover:bg-gray-200"
              }`}
            onClick={() => setEmailEventPeriod("last_7_days")}
            type="button"
            variant="ghost"
          >
            Last 7 Days
          </Button>

          <Button
            className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap
              ${
                selectedEmailEventPeriod === "last_30_days"
                  ? "bg-white shadow text-black hover:bg-gray-50"
                  : "text-gray-500 hover:bg-gray-200"
              }`}
            onClick={() => setEmailEventPeriod("last_30_days")}
            type="button"
            variant="ghost"
          >
            Last 30 Days
          </Button>

          <Button
            className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap
              ${
                selectedEmailEventPeriod === "last_90_days"
                  ? "bg-white shadow text-black hover:bg-gray-50"
                  : "text-gray-500 hover:bg-gray-200"
              }`}
            onClick={() => setEmailEventPeriod("last_90_days")}
            ref={lastItemRef}
            type="button"
            variant="ghost"
          >
            Last 90 Days
          </Button>
        </div>
      </div>
    </div>
  );
}
