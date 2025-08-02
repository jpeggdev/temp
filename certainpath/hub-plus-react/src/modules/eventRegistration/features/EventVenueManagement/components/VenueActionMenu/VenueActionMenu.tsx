import React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { EllipsisVerticalIcon } from "@heroicons/react/24/outline";

interface VenueActionMenuProps {
  venueId: number;
  onEditVenue: (id: number) => void;
  onDeleteVenue: (id: number) => void;
}

const VenueActionMenu: React.FC<VenueActionMenuProps> = ({
  venueId,
  onEditVenue,
  onDeleteVenue,
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
          onClick={() => onEditVenue(venueId)}
        >
          <span>Edit</span>
        </DropdownMenuItem>
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onDeleteVenue(venueId)}
        >
          <span>Delete</span>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default VenueActionMenu;
