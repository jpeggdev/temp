import React, { useState } from "react";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
import { Users, Tag } from "lucide-react";
import type { EventCheckoutSessionAttendee } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/types";
import WaitlistModal from "@/modules/eventRegistration/features/EventRegistration/components/WaitlistModal/WaitlistModal";

interface OrderSummaryProps {
  eventName: string;
  eventPrice: number;
  paidAttendeeCount: number;
  waitlistedAttendeeCount: number;
  allAttendees: EventCheckoutSessionAttendee[];
  occupiedAttendeeSeatsByCurrentUser: number;
  userHeldSeats?: number;
  availableSeats?: number;
  voucherSeatsUsed: number;
  codeDiscountAmount: number;
  adminDiscountAmount: number;
  total: number;
  timeLeft?: number | null;
  formatTimeLeft?: (ms: number) => string;
  checkoutSessionUuid: string;
}

export default function OrderSummary({
  eventName,
  eventPrice,
  paidAttendeeCount,
  waitlistedAttendeeCount,
  allAttendees,
  occupiedAttendeeSeatsByCurrentUser,
  userHeldSeats = 0,
  availableSeats = 0,
  voucherSeatsUsed,
  codeDiscountAmount,
  adminDiscountAmount,
  total,
  timeLeft,
  formatTimeLeft,
  checkoutSessionUuid,
}: OrderSummaryProps) {
  const [showWaitlistModal, setShowWaitlistModal] = useState(false);
  const subtotal = eventPrice * paidAttendeeCount;
  const voucherDiscount =
    eventPrice * Math.min(voucherSeatsUsed, paidAttendeeCount);

  const handleViewWaitlist = () => {
    setShowWaitlistModal(true);
  };

  return (
    <>
      <Card>
        <CardHeader>
          <CardTitle>Order Summary</CardTitle>
          <CardDescription>Review your registration details</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <Users className="mr-2 h-4 w-4 text-muted-foreground" />
                <span>
                  {paidAttendeeCount} Attendee
                  {paidAttendeeCount !== 1 ? "s" : ""} (Paid)
                </span>
              </div>
              <Badge variant="outline">{eventName}</Badge>
            </div>
            {waitlistedAttendeeCount > 0 && (
              <div className="text-sm text-amber-600 flex items-center justify-between">
                <div>
                  {waitlistedAttendeeCount} attendee
                  {waitlistedAttendeeCount !== 1 ? "s" : ""} on waitlist
                </div>
                <button
                  className="underline text-blue-600 dark:text-blue-400"
                  onClick={handleViewWaitlist}
                  type="button"
                >
                  Manage waitlist
                </button>
              </div>
            )}
            <Separator />
            <div className="space-y-2">
              <div className="flex justify-between">
                <span>Registration Fee</span>
                <span>
                  ${eventPrice.toFixed(2)} × {paidAttendeeCount}
                </span>
              </div>
              <div className="flex justify-between">
                <span>Subtotal</span>
                <span>${subtotal.toFixed(2)}</span>
              </div>
              {voucherSeatsUsed > 0 && (
                <div className="flex flex-col space-y-1 text-green-600">
                  <div className="flex justify-between">
                    <span className="flex items-center font-medium">
                      <Tag className="mr-1 h-4 w-4" />
                      Voucher applied
                    </span>
                    <span>-${voucherDiscount.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-end text-xs text-muted-foreground">
                    {Math.min(voucherSeatsUsed, paidAttendeeCount)} seat
                    {Math.min(voucherSeatsUsed, paidAttendeeCount) !== 1
                      ? "s"
                      : ""}
                  </div>
                </div>
              )}
              {codeDiscountAmount > 0 && (
                <div className="flex flex-col space-y-1 text-blue-600">
                  <div className="flex justify-between">
                    <span className="flex items-center font-medium">
                      <Tag className="mr-1 h-4 w-4" />
                      Discount code
                    </span>
                    <span>-${codeDiscountAmount.toFixed(2)}</span>
                  </div>
                </div>
              )}
              {adminDiscountAmount > 0 && (
                <div className="flex flex-col space-y-1 text-purple-600">
                  <div className="flex justify-between">
                    <span className="flex items-center font-medium">
                      <Tag className="mr-1 h-4 w-4" />
                      Admin discount
                    </span>
                    <span>-${adminDiscountAmount.toFixed(2)}</span>
                  </div>
                </div>
              )}
              <Separator />
              <div className="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>${total.toFixed(2)}</span>
              </div>
            </div>
            <Separator className="my-2" />
            {userHeldSeats > 0 ? (
              <div className="text-sm text-green-700 dark:text-green-500 space-y-1">
                <div className="flex justify-between">
                  <span>You have {userHeldSeats} seat(s) held</span>
                  {timeLeft && formatTimeLeft && timeLeft > 0 && (
                    <span className="text-amber-600 text-xs">
                      Expires in {formatTimeLeft(timeLeft)}
                    </span>
                  )}
                </div>
                <div className="flex justify-between">
                  <span>Still available for others:</span>
                  {availableSeats > 0 ? (
                    <span>{availableSeats}</span>
                  ) : (
                    <span className="text-amber-600">Waitlist only</span>
                  )}
                </div>
              </div>
            ) : (
              <div className="text-sm space-y-1">
                <div className="flex justify-between">
                  <span>Available seats:</span>
                  {availableSeats > 0 ? (
                    <span>{availableSeats}</span>
                  ) : (
                    <span className="text-amber-600">Waitlist only</span>
                  )}
                </div>
              </div>
            )}
          </div>
        </CardContent>
      </Card>
      <WaitlistModal
        allAttendees={allAttendees}
        checkoutSessionUuid={checkoutSessionUuid}
        eventName={eventName}
        isOpen={showWaitlistModal}
        occupiedAttendeeSeatsByCurrentUser={occupiedAttendeeSeatsByCurrentUser}
        onClose={() => setShowWaitlistModal(false)}
      />
    </>
  );
}
