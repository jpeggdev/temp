import React from "react";
import { Calendar, MapPin } from "lucide-react";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";

type EventDetailsCardProps = {
  eventName?: string;
  eventSessionName?: string;
  startDate?: string;
  eventPrice: number;
  availableSeats: number;
  reservationExpiresAt?: string;
  existingCheckoutSessionUuid?: string | null;
};

export function EventDetailsCard({
  eventName,
  eventSessionName,
  startDate,
  eventPrice,
  availableSeats,
  reservationExpiresAt,
  existingCheckoutSessionUuid,
}: EventDetailsCardProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Event Details</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <div>
          <h3 className="font-semibold text-lg">{eventName || "N/A"}</h3>
          <p className="text-muted-foreground">{eventSessionName || "N/A"}</p>
        </div>

        <div className="flex items-center gap-2 text-muted-foreground">
          <Calendar className="h-4 w-4" />
          <span>
            {startDate
              ? new Date(startDate).toLocaleDateString("en-US", {
                  month: "long",
                  day: "numeric",
                  year: "numeric",
                })
              : "N/A"}
          </span>
        </div>

        <div className="flex items-center gap-2 text-muted-foreground">
          <MapPin className="h-4 w-4" />
        </div>

        <div className="mt-4 pt-4 border-t">
          <div className="flex justify-between">
            <span>Price per attendee:</span>
            <span className="font-semibold">${eventPrice.toFixed(2)}</span>
          </div>
          <div className="flex justify-between mt-2">
            <span>Available seats:</span>
            <span className="font-semibold">{availableSeats}</span>
          </div>

          {reservationExpiresAt && existingCheckoutSessionUuid && (
            <div className="text-xs text-muted-foreground mt-3">
              <div className="flex justify-between">
                <span>Reservation expires:</span>
                <span>{new Date(reservationExpiresAt).toLocaleString()}</span>
              </div>
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
