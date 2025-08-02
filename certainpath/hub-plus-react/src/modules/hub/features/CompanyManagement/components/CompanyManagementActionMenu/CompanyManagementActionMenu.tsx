import React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { MoreHorizontal, Edit } from "lucide-react";

interface CompanyManagementActionMenuProps {
  companyUuid: string;
  onEdit: (uuid: string) => void;
}

const CompanyManagementActionMenu: React.FC<
  CompanyManagementActionMenuProps
> = ({ companyUuid, onEdit }) => {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          className="h-8 w-8 p-0 hover:bg-gray-200 transition-colors"
          variant="ghost"
        >
          <span className="sr-only">Open menu</span>
          <MoreHorizontal className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-[160px] p-2 bg-white">
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onEdit(companyUuid)}
        >
          <Edit className="mr-2 h-4 w-4" />
          <span>Edit Company</span>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default CompanyManagementActionMenu;
