import React from "react";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { EventEnrollmentItemResponseDTO } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventEnrollments/types";

interface MoveToWaitlistDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  isMovingToWaitlist: boolean;
  selectedRegistration: EventEnrollmentItemResponseDTO | null;
  moveToWaitlist: (registration: EventEnrollmentItemResponseDTO) => void;
}

export default function MoveToWaitlistDialog({
  open,
  onOpenChange,
  isMovingToWaitlist,
  selectedRegistration,
  moveToWaitlist,
}: MoveToWaitlistDialogProps) {
  return (
    <AlertDialog onOpenChange={onOpenChange} open={open}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Move to Waitlist</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to move{" "}
            {selectedRegistration?.firstName +
              " " +
              selectedRegistration?.lastName || "this user"}{" "}
            to the waitlist? They will lose their spot in the session.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            disabled={isMovingToWaitlist}
            onClick={() => {
              if (selectedRegistration) {
                moveToWaitlist(selectedRegistration);
              }
            }}
          >
            {isMovingToWaitlist ? "Moving..." : "Move to Waitlist"}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
