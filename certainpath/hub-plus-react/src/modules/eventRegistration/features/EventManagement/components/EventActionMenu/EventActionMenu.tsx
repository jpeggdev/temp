import React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import {
  EllipsisVerticalIcon,
  PencilSquareIcon,
  DocumentDuplicateIcon,
  EyeIcon,
  TrashIcon,
} from "@heroicons/react/24/outline";

interface EventActionMenuProps {
  eventId: number;
  eventUuid: string;
  onDuplicateEvent: (eventId: number) => void;
  onEditEvent: (uuid: string) => void;
  onDeleteEvent: (id: number) => void;
  onViewSessions: (uuid: string) => void;
}

const EventActionMenu: React.FC<EventActionMenuProps> = ({
  eventId,
  eventUuid,
  onDuplicateEvent,
  onEditEvent,
  onDeleteEvent,
  onViewSessions,
}) => {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          className="h-8 w-8 p-0 hover:bg-gray-200 transition-colors"
          variant="ghost"
        >
          <span className="sr-only">Open menu</span>
          <EllipsisVerticalIcon className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>

      <DropdownMenuContent align="end" className="w-[160px] p-2 bg-white">
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onEditEvent(eventUuid)}
        >
          <PencilSquareIcon className="mr-2 h-4 w-4 text-gray-500" />
          Edit Event
        </DropdownMenuItem>
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onDuplicateEvent(eventId)}
        >
          <DocumentDuplicateIcon className="mr-2 h-4 w-4 text-gray-500" />
          Duplicate
        </DropdownMenuItem>
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onViewSessions(eventUuid)}
        >
          <EyeIcon className="mr-2 h-4 w-4 text-gray-500" />
          Sessions
        </DropdownMenuItem>
        <DropdownMenuSeparator className="my-1 h-px bg-gray-200" />
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-red-50 text-red-600 transition-colors"
          onClick={() => onDeleteEvent(eventId)}
        >
          <TrashIcon className="mr-2 h-4 w-4" />
          Delete Event
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default EventActionMenu;
