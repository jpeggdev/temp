import React, { useState, useEffect } from "react";
import {
  DndContext,
  closestCenter,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
  DragEndEvent,
} from "@dnd-kit/core";
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import {
  Table,
  TableBody,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { EventWaitlistItemResponse } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventWaitlistItems/types";
import { useToast } from "@/components/ui/use-toast";
import { SortableWaitlistRow } from "@/modules/eventRegistration/features/EventWaitlist/components/SortableWaitlistRow/SortableWaitlistRow";

interface WaitlistTableProps {
  waitlist: EventWaitlistItemResponse[];
  formatDate: (date: string) => string;
  isSessionFull: boolean;
  handleRegisterClick: (user: EventWaitlistItemResponse) => void;
  handleRemoveClick: (user: EventWaitlistItemResponse) => void;
  onPositionChange?: (waitlistId: number, newPosition: number) => Promise<void>;
  onMoveToTop?: (waitlistId: number) => Promise<void>;
}

export default function WaitlistTable({
  waitlist,
  formatDate,
  isSessionFull,
  handleRegisterClick,
  handleRemoveClick,
  onPositionChange,
  onMoveToTop,
}: WaitlistTableProps) {
  const [items, setItems] = useState<EventWaitlistItemResponse[]>(waitlist);
  const { toast } = useToast();

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 8, // 8px movement required before drag starts
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  // Update items when waitlist prop changes
  useEffect(() => {
    setItems(waitlist);
  }, [waitlist]);

  const handleDragEnd = async (event: DragEndEvent) => {
    const { active, over } = event;

    if (over && active.id !== over.id) {
      const oldIndex = items.findIndex((item) => item.id === active.id);
      const newIndex = items.findIndex((item) => item.id === over.id);

      // Update the local state immediately for a responsive UI
      const newItems = arrayMove(items, oldIndex, newIndex);
      setItems(newItems);

      try {
        // Call the API to update the position
        if (onPositionChange) {
          await onPositionChange(Number(active.id), newIndex + 1);
        }

        toast({
          title: "Position updated",
          description: "Waitlist position updated successfully",
        });
      } catch (error) {
        toast({
          title: "Error updating position",
          description:
            error instanceof Error ? error.message : "An error occurred",
          variant: "destructive",
        });

        // Reset to the original waitlist order on error
        setItems(waitlist);
      }
    }
  };

  const handleMoveUp = async (id: number) => {
    const index = items.findIndex((item) => item.id === id);
    if (index <= 0) return; // Can't move up if already at the top

    try {
      // Update the local state immediately
      const newItems = arrayMove(items, index, index - 1);
      setItems(newItems);

      // Call the API to update the position
      if (onPositionChange) {
        await onPositionChange(id, index);
      }

      toast({
        title: "Moved up",
        description: "Entry moved up successfully",
      });
    } catch (error) {
      toast({
        title: "Error moving entry",
        description:
          error instanceof Error ? error.message : "An error occurred",
        variant: "destructive",
      });

      // Reset to the original waitlist order on error
      setItems(waitlist);
    }
  };

  const handleMoveDown = async (id: number) => {
    const index = items.findIndex((item) => item.id === id);
    if (index === -1 || index >= items.length - 1) return; // Can't move down if already at the bottom

    try {
      // Update the local state immediately
      const newItems = arrayMove(items, index, index + 1);
      setItems(newItems);

      // Call the API to update the position
      if (onPositionChange) {
        await onPositionChange(id, index + 2);
      }

      toast({
        title: "Moved down",
        description: "Entry moved down successfully",
      });
    } catch (error) {
      toast({
        title: "Error moving entry",
        description:
          error instanceof Error ? error.message : "An error occurred",
        variant: "destructive",
      });

      // Reset to the original waitlist order on error
      setItems(waitlist);
    }
  };

  const handleMoveToTop = async (id: number) => {
    const index = items.findIndex((item) => item.id === id);
    if (index <= 0) return; // Already at the top

    try {
      // Update the local state immediately
      const newItems = [...items];
      const item = newItems.splice(index, 1)[0];
      newItems.unshift(item);
      setItems(newItems);

      // Call the API to update the position
      if (onMoveToTop) {
        await onMoveToTop(id);
      }

      toast({
        title: "Moved to top",
        description: "Entry moved to top of waitlist",
      });
    } catch (error) {
      toast({
        title: "Error moving entry",
        description:
          error instanceof Error ? error.message : "An error occurred",
        variant: "destructive",
      });

      // Reset to the original waitlist order on error
      setItems(waitlist);
    }
  };

  return (
    <div className="border rounded-md">
      <DndContext
        collisionDetection={closestCenter}
        onDragEnd={handleDragEnd}
        sensors={sensors}
      >
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-14"></TableHead>
              <TableHead>Position</TableHead>
              <TableHead>User</TableHead>
              <TableHead>Waitlisted On</TableHead>
              <TableHead className="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <SortableContext
            items={items.map((i) => i.id)}
            strategy={verticalListSortingStrategy}
          >
            <TableBody>
              {items.map((user, index) => (
                <SortableWaitlistRow
                  formatDate={formatDate}
                  handleRegisterClick={handleRegisterClick}
                  handleRemoveClick={handleRemoveClick}
                  isFirstItem={index === 0}
                  isLastItem={index === items.length - 1}
                  isSessionFull={isSessionFull}
                  key={user.id}
                  onMoveDown={handleMoveDown}
                  onMoveToTop={handleMoveToTop}
                  onMoveUp={handleMoveUp}
                  user={user}
                />
              ))}
            </TableBody>
          </SortableContext>
        </Table>
      </DndContext>
    </div>
  );
}
