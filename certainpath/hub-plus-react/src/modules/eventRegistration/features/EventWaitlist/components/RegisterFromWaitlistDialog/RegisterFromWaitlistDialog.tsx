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

interface RegisterFromWaitlistDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  isRegistering: boolean;
  selectedUser: EventWaitlistItemResponse | null;
  registerFromWaitlist: (id: number) => void;
}

export default function RegisterFromWaitlistDialog({
  open,
  onOpenChange,
  isRegistering,
  selectedUser,
  registerFromWaitlist,
}: RegisterFromWaitlistDialogProps) {
  return (
    <AlertDialog onOpenChange={onOpenChange} open={open}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Register from Waitlist</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to register{" "}
            {selectedUser
              ? selectedUser.firstName + " " + selectedUser.lastName
              : "this user"}{" "}
            from the waitlist? They will be moved to the registered users list.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            disabled={isRegistering}
            onClick={() => {
              if (selectedUser) {
                registerFromWaitlist(selectedUser.id);
              }
            }}
          >
            {isRegistering ? "Registering..." : "Register"}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
