import React from "react";
import { Badge } from "@/components/ui/badge";
import { Calendar as CalendarIcon, Clock } from "lucide-react";
import {
  formatDateRangeInTimeZone,
  formatTimeRangeInTimeZone,
} from "@/modules/eventRegistration/features/EventDirectory/utils/dateUtils";

function EventDetailsHeader({
  eventData,
  sessionData,
}: {
  eventData?: {
    title?: string | null;
    accepts_vouchers?: boolean;
  } | null;
  sessionData?: {
    title?: string | null;
    start_time?: string | null;
    end_time?: string | null;
    timezoneIdentifier?: string | null;
    timezoneShortName?: string | null;
  } | null;
}) {
  const dateRange = formatDateRangeInTimeZone(
    sessionData?.start_time || null,
    sessionData?.end_time || null,
    sessionData?.timezoneIdentifier || null,
  );

  let timeRange = formatTimeRangeInTimeZone(
    sessionData?.start_time || null,
    sessionData?.end_time || null,
    sessionData?.timezoneIdentifier || null,
  );
  if (sessionData?.timezoneShortName) {
    timeRange += ` (${sessionData.timezoneShortName})`;
  }

  return (
    <div className="mb-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">
            {eventData?.title || "N/A"}
          </h1>
          {sessionData?.title && sessionData.title !== eventData?.title && (
            <p className="text-muted-foreground">{sessionData.title}</p>
          )}

          <div className="flex flex-wrap gap-2 mt-2">
            {sessionData?.start_time && (
              <Badge className="flex items-center gap-1" variant="outline">
                <CalendarIcon className="h-3 w-3" />
                {dateRange}
              </Badge>
            )}

            {sessionData?.start_time && sessionData?.end_time && (
              <Badge className="flex items-center gap-1" variant="outline">
                <Clock className="h-3 w-3" />
                {timeRange}
              </Badge>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default EventDetailsHeader;
