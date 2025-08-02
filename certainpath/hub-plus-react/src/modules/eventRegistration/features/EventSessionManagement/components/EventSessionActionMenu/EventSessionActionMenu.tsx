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
  ClipboardDocumentListIcon,
} from "@heroicons/react/24/outline";

interface EventSessionActionMenuProps {
  sessionUuid: string;
  onDeleteSession: (uuid: string) => void;
  onEditSession?: (uuid: string) => void;
  onDuplicateSession?: (uuid: string) => void;
  onViewAttendees?: (uuid: string) => void;
  onWaitlistSession?: (uuid: string) => void;
}

const EventSessionActionMenu: React.FC<EventSessionActionMenuProps> = ({
  sessionUuid,
  onDeleteSession,
  onEditSession,
  onDuplicateSession,
  onViewAttendees,
  onWaitlistSession,
}) => {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          className="h-8 w-8 p-0 transition-colors hover:bg-gray-200"
          variant="ghost"
        >
          <span className="sr-only">Open session actions</span>
          <EllipsisVerticalIcon className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>

      <DropdownMenuContent align="end" className="w-40 p-2 bg-white">
        {onEditSession && (
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer transition-colors hover:bg-gray-100"
            onClick={() => onEditSession(sessionUuid)}
          >
            <PencilSquareIcon className="mr-2 h-4 w-4 text-gray-500" />
            Edit Session
          </DropdownMenuItem>
        )}

        {onDuplicateSession && (
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer transition-colors hover:bg-gray-100"
            onClick={() => onDuplicateSession(sessionUuid)}
          >
            <DocumentDuplicateIcon className="mr-2 h-4 w-4 text-gray-500" />
            Duplicate
          </DropdownMenuItem>
        )}

        {onViewAttendees && (
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer transition-colors hover:bg-gray-100"
            onClick={() => onViewAttendees(sessionUuid)}
          >
            <EyeIcon className="mr-2 h-4 w-4 text-gray-500" />
            Attendees
          </DropdownMenuItem>
        )}

        {onWaitlistSession && (
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer transition-colors hover:bg-gray-100"
            onClick={() => onWaitlistSession(sessionUuid)}
          >
            <ClipboardDocumentListIcon className="mr-2 h-4 w-4 text-gray-500" />
            Waitlist
          </DropdownMenuItem>
        )}

        <DropdownMenuSeparator className="my-1 h-px bg-gray-200" />

        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer text-red-600 transition-colors hover:bg-red-50"
          onClick={() => onDeleteSession(sessionUuid)}
        >
          <TrashIcon className="mr-2 h-4 w-4" />
          Delete Session
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default EventSessionActionMenu;
