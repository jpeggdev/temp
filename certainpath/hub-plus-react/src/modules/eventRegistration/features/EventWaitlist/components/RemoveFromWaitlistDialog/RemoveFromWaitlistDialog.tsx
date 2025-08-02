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
import { EventWaitlistItemResponse } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventWaitlistItems/types";

interface RemoveFromWaitlistDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  user: EventWaitlistItemResponse | null;
  removeFromWaitlist: (id: number) => void;
}

export default function RemoveFromWaitlistDialog({
  open,
  onOpenChange,
  user,
  removeFromWaitlist,
}: RemoveFromWaitlistDialogProps) {
  return (
    <AlertDialog onOpenChange={onOpenChange} open={open}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Remove from Waitlist</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to remove{" "}
            {user ? user.firstName + " " + user.lastName : "this user"} from the
            waitlist? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            onClick={() => {
              if (user) {
                removeFromWaitlist(user.id);
              }
            }}
          >
            Remove
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
