import React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { EllipsisVerticalIcon } from "@heroicons/react/24/outline";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Copy, Edit, Trash2 } from "lucide-react";

interface EmailTemplateManagementActionMenuProps {
  emailTemplateId: number;
  onEditEmailTemplate: (id: number) => void;
  onDuplicateEmailTemplate: (id: number) => void;
  onDeleteEmailTemplate: (id: number) => void;
}

const EmailTemplateActionMenu: React.FC<
  EmailTemplateManagementActionMenuProps
> = ({
  emailTemplateId,
  onEditEmailTemplate,
  onDeleteEmailTemplate,
  onDuplicateEmailTemplate,
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
        <ShowIfHasAccess requiredPermissions={["CAN_MANAGE_CAMPAIGN_BATCHES"]}>
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
            onClick={() => onEditEmailTemplate(emailTemplateId)}
          >
            <Edit className="mr-2 h-4 w-4" />
            <span>Edit</span>
          </DropdownMenuItem>
        </ShowIfHasAccess>

        <ShowIfHasAccess requiredPermissions={["CAN_MANAGE_CAMPAIGN_BATCHES"]}>
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
            onClick={() => onDuplicateEmailTemplate(emailTemplateId)}
          >
            <Copy className="mr-2 h-4 w-4" />
            <span>Duplicate</span>
          </DropdownMenuItem>
        </ShowIfHasAccess>

        <ShowIfHasAccess requiredPermissions={["CAN_EDIT_CAMPAIGNS"]}>
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer text-red-600 hover:bg-gray-100 transition-colors"
            onClick={() => onDeleteEmailTemplate(emailTemplateId)}
          >
            <Trash2 className="mr-2 h-4 w-4" />
            <span>Delete</span>
          </DropdownMenuItem>
        </ShowIfHasAccess>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default EmailTemplateActionMenu;
