import React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { EllipsisVerticalIcon } from "@heroicons/react/24/outline";

interface EventInstructorActionMenuProps {
  instructorId: number;
  onEditInstructor: (id: number) => void;
  onDeleteInstructor: (id: number) => void;
}

const EventInstructorActionMenu: React.FC<EventInstructorActionMenuProps> = ({
  instructorId,
  onEditInstructor,
  onDeleteInstructor,
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

      <DropdownMenuContent align="end" className="w-[150px] p-2 bg-white">
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onEditInstructor(instructorId)}
        >
          Edit Instructor
        </DropdownMenuItem>
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onDeleteInstructor(instructorId)}
        >
          Delete Instructor
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default EventInstructorActionMenu;
