import React from "react";
import { Column } from "@/components/Datatable/types";
import { Switch } from "@/components/ui/switch";
import { formatCurrency } from "@/utils/formatCurrency";
import { EventItem } from "../../slices/eventListSlice";
import EventActionMenu from "../EventActionMenu/EventActionMenu";

interface CreateEventColumnsProps {
  onTogglePublished: (uuid: string, newVal: boolean) => void;
  onDuplicateEvent: (eventId: number) => void;
  onEditEvent: (uuid: string) => void;
  onDeleteEvent: (id: number) => void;
  onViewSessions: (uuid: string) => void; // <--- newly added
}

export function createEventColumns({
  onTogglePublished,
  onDuplicateEvent,
  onEditEvent,
  onDeleteEvent,
  onViewSessions,
}: CreateEventColumnsProps): Column<EventItem>[] {
  return [
    {
      header: "Event Name",
      accessorKey: "eventName",
      enableSorting: true,
    },
    {
      header: "Event Code",
      accessorKey: "eventCode",
      enableSorting: true,
    },
    {
      header: "Event Type",
      accessorKey: "eventTypeName",
      cell: ({ row }) => row.original.eventTypeName || "--",
    },
    {
      header: "Category",
      accessorKey: "eventCategoryName",
      cell: ({ row }) => row.original.eventCategoryName || "--",
    },
    {
      header: "Price",
      accessorKey: "eventPrice",
      enableSorting: true,
      cell: ({ row }) => formatCurrency(row.original.eventPrice),
    },
    {
      header: "Status",
      accessorKey: "isPublished",
      enableSorting: true,
      cell: ({ row }) => {
        const isPublished = row.original.isPublished;
        const uuid = row.original.uuid;
        const handleChange = (checked: boolean) => {
          onTogglePublished(uuid, checked);
        };
        const stopPropagation = (evt: React.MouseEvent) => {
          evt.stopPropagation();
        };

        return (
          <Switch
            checked={isPublished}
            onCheckedChange={handleChange}
            onClick={stopPropagation}
          />
        );
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <EventActionMenu
          eventId={row.original.id}
          eventUuid={row.original.uuid}
          onDeleteEvent={onDeleteEvent}
          onDuplicateEvent={onDuplicateEvent}
          onEditEvent={onEditEvent}
          onViewSessions={onViewSessions}
        />
      ),
    },
  ];
}
