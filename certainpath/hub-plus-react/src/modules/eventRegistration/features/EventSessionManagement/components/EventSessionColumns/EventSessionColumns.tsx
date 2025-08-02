import React from "react";
import { Column } from "@/components/Datatable/types";
import { Switch } from "@/components/ui/switch";
import EventSessionActionMenu from "@/modules/eventRegistration/features/EventSessionManagement/components/EventSessionActionMenu/EventSessionActionMenu";
import { SessionSummary } from "@/modules/eventRegistration/features/EventSessionManagement/api/fetchEventSessions/types";

function formatSessionDateTime(
  dateStr?: string | null,
  tzIdentifier?: string | null,
  tzShortName?: string | null,
): string {
  if (!dateStr) return "--";

  const date = new Date(dateStr);
  if (isNaN(date.getTime())) return "--";

  let dateTimeFormatted = "";
  if (!tzIdentifier) {
    dateTimeFormatted = date.toLocaleString([], {
      weekday: "short",
      month: "short",
      day: "numeric",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    });
  } else {
    dateTimeFormatted = new Intl.DateTimeFormat("en-US", {
      timeZone: tzIdentifier,
      weekday: "short",
      month: "short",
      day: "numeric",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    }).format(date);
  }

  if (tzShortName) {
    dateTimeFormatted += ` (${tzShortName})`;
  }

  return dateTimeFormatted;
}

interface CreateEventSessionColumnsProps {
  onDeleteSession: (uuid: string) => void;
  onEditSession?: (uuid: string) => void;
  onTogglePublishedSession: (uuid: string, newVal: boolean) => void;
  onWaitlistSession?: (uuid: string) => void;
}

export function createEventSessionColumns({
  onDeleteSession,
  onEditSession,
  onTogglePublishedSession,
  onWaitlistSession,
}: CreateEventSessionColumnsProps): Column<SessionSummary>[] {
  return [
    {
      header: "Start Date",
      accessorKey: "startDate",
      enableSorting: true,
      cell: ({ row }) => {
        const { startDate, timezoneIdentifier, timezoneShortName } =
          row.original;
        return formatSessionDateTime(
          startDate,
          timezoneIdentifier,
          timezoneShortName,
        );
      },
    },
    {
      header: "End Date",
      accessorKey: "endDate",
      enableSorting: true,
      cell: ({ row }) => {
        const { endDate, timezoneIdentifier, timezoneShortName } = row.original;
        return formatSessionDateTime(
          endDate,
          timezoneIdentifier,
          timezoneShortName,
        );
      },
    },
    {
      header: "Max Enrollments",
      accessorKey: "maxEnrollments",
      enableSorting: true,
      cell: ({ row }) => row.original.maxEnrollments ?? "--",
    },
    {
      header: "Published",
      accessorKey: "isPublished",
      enableSorting: true,
      cell: ({ row }) => {
        const { uuid, isPublished } = row.original;

        const stopPropagation = (evt: React.MouseEvent) => {
          evt.stopPropagation();
        };

        const handleChange = (checked: boolean) => {
          onTogglePublishedSession(uuid, checked);
        };

        return (
          <Switch
            checked={Boolean(isPublished)}
            onCheckedChange={handleChange}
            onClick={stopPropagation}
          />
        );
      },
    },
    {
      header: "Actions",
      id: "actions",
      cell: ({ row }) => (
        <EventSessionActionMenu
          onDeleteSession={onDeleteSession}
          onEditSession={onEditSession}
          onWaitlistSession={onWaitlistSession} // PASS ALONG NEW PROP
          sessionUuid={row.original.uuid}
        />
      ),
    },
  ];
}
