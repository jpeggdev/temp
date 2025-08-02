import React from "react";
import { CheckCircle2, XCircle, Loader2 } from "lucide-react";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";

interface ContinueSessionModalProps {
  isOpen: boolean;
  eventName: string;
  createdAt: string | null;
  onContinue: () => void;
  onStartNew: () => void;
  isContinuing?: boolean;
}

export function ContinueSessionModal({
  isOpen,
  eventName,
  createdAt,
  onContinue,
  onStartNew,
  isContinuing = false,
}: ContinueSessionModalProps) {
  const formattedCreationDate = createdAt
    ? new Date(createdAt).toLocaleString()
    : "";

  return (
    <Dialog onOpenChange={() => {}} open={isOpen}>
      <DialogContent hideCloseButton>
        <DialogHeader>
          <DialogTitle>Continue Your Registration?</DialogTitle>
          <DialogDescription>
            We found an existing registration in progress for this event.
          </DialogDescription>
        </DialogHeader>

        <div className="py-4">
          <p className="font-semibold">{eventName || "Event"}</p>
          <p className="text-sm text-muted-foreground mt-1">
            Started on {formattedCreationDate}
          </p>

          <p className="mt-4">
            Would you like to continue where you left off, or start a new
            registration?
          </p>
        </div>

        <DialogFooter className="gap-2 sm:gap-0">
          <Button
            className="flex items-center gap-2"
            disabled={isContinuing}
            onClick={onStartNew}
            variant="outline"
          >
            <XCircle className="h-4 w-4" />
            Start New
          </Button>
          <Button
            className="flex items-center gap-2"
            disabled={isContinuing}
            onClick={onContinue}
          >
            {isContinuing ? (
              <>
                <Loader2 className="h-4 w-4 animate-spin" />
                Loading...
              </>
            ) : (
              <>
                <CheckCircle2 className="h-4 w-4" />
                Continue
              </>
            )}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
