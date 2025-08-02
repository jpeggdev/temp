import React from "react";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { TableRow, TableCell } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  GripVertical,
  ChevronUp,
  ChevronDown,
  ChevronsUp,
  UserCheck,
  UserMinus,
} from "lucide-react";
import { EventWaitlistItemResponse } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventWaitlistItems/types";

interface SortableWaitlistRowProps {
  user: EventWaitlistItemResponse;
  onMoveUp: (id: number) => void;
  onMoveDown: (id: number) => void;
  onMoveToTop: (id: number) => void;
  handleRegisterClick: (user: EventWaitlistItemResponse) => void;
  handleRemoveClick: (user: EventWaitlistItemResponse) => void;
  isFirstItem: boolean;
  isLastItem: boolean;
  isSessionFull: boolean;
  formatDate: (date: string) => string;
}

export function SortableWaitlistRow({
  user,
  onMoveUp,
  onMoveDown,
  onMoveToTop,
  handleRegisterClick,
  handleRemoveClick,
  isFirstItem,
  isLastItem,
  isSessionFull,
  formatDate,
}: SortableWaitlistRowProps) {
  const { attributes, listeners, setNodeRef, transform, transition } =
    useSortable({ id: user.id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  const userName =
    [user.firstName, user.lastName].filter(Boolean).join(" ") || "Unknown";

  return (
    <TableRow className="group hover:bg-gray-50" ref={setNodeRef} style={style}>
      <TableCell className="w-14">
        <span
          {...attributes}
          {...listeners}
          className="flex h-8 w-8 cursor-grab items-center justify-center rounded-md border border-gray-200 bg-white p-1 text-gray-400 shadow-sm hover:bg-gray-50 active:cursor-grabbing"
        >
          <GripVertical className="h-4 w-4" />
        </span>
      </TableCell>
      <TableCell className="font-medium">
        <Badge variant="outline">{user.waitlistPosition || "-"}</Badge>
      </TableCell>
      <TableCell>{userName}</TableCell>
      <TableCell>
        {user.waitlistedAt ? formatDate(user.waitlistedAt) : "Unknown Date"}
      </TableCell>
      <TableCell className="text-right">
        <div className="flex justify-end gap-2">
          <Button
            disabled={isFirstItem}
            onClick={() => onMoveUp(user.id)}
            size="sm"
            title="Move up one position"
            variant="outline"
          >
            <ChevronUp className="h-4 w-4" />
          </Button>
          <Button
            disabled={isLastItem}
            onClick={() => onMoveDown(user.id)}
            size="sm"
            title="Move down one position"
            variant="outline"
          >
            <ChevronDown className="h-4 w-4" />
          </Button>
          <Button
            disabled={isFirstItem}
            onClick={() => onMoveToTop(user.id)}
            size="sm"
            title="Move to top of waitlist"
            variant="outline"
          >
            <ChevronsUp className="h-4 w-4" />
          </Button>
          <Button
            disabled={isSessionFull}
            onClick={() => handleRegisterClick(user)}
            size="sm"
            title={
              isSessionFull ? "Session is full" : "Register user from waitlist"
            }
            variant="outline"
          >
            <UserCheck className="h-4 w-4" />
          </Button>
          <Button
            className="text-red-500 hover:text-red-700 hover:bg-red-50"
            onClick={() => handleRemoveClick(user)}
            size="sm"
            title="Remove from waitlist"
            variant="outline"
          >
            <UserMinus className="h-4 w-4" />
          </Button>
        </div>
      </TableCell>
    </TableRow>
  );
}
