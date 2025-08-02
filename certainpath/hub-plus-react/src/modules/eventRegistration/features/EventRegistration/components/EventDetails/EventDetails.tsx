import React from "react";
import { Calendar, MapPin } from "lucide-react";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import {
  formatDateTimeRangeInTimeZone,
  formatLocalTimeRange,
  getLocalTimeZoneShortName,
} from "@/modules/eventRegistration/features/EventDirectory/utils/dateUtils";
import { EventCheckoutSessionVenue } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/types";

type GetDetailsData = {
  eventName?: string | null;
  eventSessionName?: string | null;
  startDate?: string | null;
  endDate?: string | null;
  reservationExpiresAt?: string | null;
  venue?: EventCheckoutSessionVenue | null;
  timezoneIdentifier?: string | null;
  timezoneShortName?: string | null;
  isVirtualOnly?: boolean;
  occupiedAttendeeSeatsByCurrentUser?: number;
};

type EventDetailsProps = {
  getDetailsData: GetDetailsData | null;
  existingCheckoutSessionUuid: string | null;
  eventPrice: number;
  availableSeats: number;
  timeLeft: number | null;
  formatTimeLeft: (ms: number) => string;
};

function buildMapsUrl(
  address?: string | null,
  city?: string | null,
  state?: string | null,
  postalCode?: string | null,
): string {
  const parts = [address, city, state, postalCode].filter(Boolean).join(", ");
  return `https://www.google.com/maps?q=${encodeURIComponent(parts)}`;
}

function EventDetails({
  getDetailsData,
  eventPrice,
  availableSeats,
  timeLeft,
  formatTimeLeft,
}: EventDetailsProps) {
  const localShortName = getLocalTimeZoneShortName();
  const isVirtual = getDetailsData?.isVirtualOnly ?? false;
  const venue = getDetailsData?.venue ?? null;
  const sessionTimezoneRange = formatDateTimeRangeInTimeZone(
    getDetailsData?.startDate || null,
    getDetailsData?.endDate || null,
    getDetailsData?.timezoneIdentifier || null,
  );
  const localTimeRange = formatLocalTimeRange(
    getDetailsData?.startDate || null,
    getDetailsData?.endDate || null,
  );
  const mapsUrl = venue
    ? buildMapsUrl(venue.address, venue.city, venue.state, venue.postalCode)
    : "";
  const userHeldSeats = getDetailsData?.occupiedAttendeeSeatsByCurrentUser ?? 0;

  let venueElement;
  if (isVirtual) {
    venueElement = (
      <span className="flex gap-2 items-center text-sm text-muted-foreground">
        <MapPin className="h-4 w-4" />
        <span>Virtual-Only</span>
      </span>
    );
  } else if (venue) {
    venueElement = (
      <div className="flex flex-col text-sm text-muted-foreground gap-1">
        <div className="flex gap-2 items-center">
          <MapPin className="h-4 w-4" />
          <span>{venue.name || "Unnamed Venue"}</span>
        </div>
        {venue.address && (
          <a
            className="underline text-blue-600 dark:text-blue-400 ml-6"
            href={mapsUrl}
            rel="noopener noreferrer"
            target="_blank"
          >
            {venue.address}
          </a>
        )}
        {venue.city && (
          <a
            className="underline text-blue-600 dark:text-blue-400 ml-6"
            href={mapsUrl}
            rel="noopener noreferrer"
            target="_blank"
          >
            {venue.city}
            {venue.state ? `, ${venue.state}` : ""}
            {venue.postalCode ? ` ${venue.postalCode}` : ""}
          </a>
        )}
      </div>
    );
  } else {
    venueElement = (
      <span className="flex gap-2 items-center text-sm text-muted-foreground italic">
        <MapPin className="h-4 w-4" />
        <span>Not specified</span>
      </span>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Event Details</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <div>
          <h3 className="font-semibold text-lg">
            {getDetailsData?.eventName || "N/A"}
          </h3>
          <p className="text-muted-foreground">
            {getDetailsData?.eventSessionName || "N/A"}
          </p>
        </div>
        {getDetailsData?.startDate ? (
          <div className="flex flex-col gap-1 text-sm text-muted-foreground">
            <div className="flex gap-2 items-center">
              <Calendar className="h-4 w-4" />
              <span>{sessionTimezoneRange}</span>
            </div>
            <span className="ml-6 text-xs">
              (Local: {localTimeRange} ({localShortName}))
            </span>
          </div>
        ) : (
          <div className="flex items-center gap-2 text-muted-foreground">
            <Calendar className="h-4 w-4" />
            <span>N/A</span>
          </div>
        )}
        <div>{venueElement}</div>

        <div className="mt-4 pt-4 border-t space-y-2 text-sm">
          <div className="flex justify-between">
            <span>Price per attendee:</span>
            <span className="font-semibold">${eventPrice.toFixed(2)}</span>
          </div>

          {userHeldSeats > 0 ? (
            <>
              <div className="flex justify-between">
                <span className="text-green-700 dark:text-green-500">
                  You have {userHeldSeats} seat(s) held
                </span>
                {timeLeft !== null && timeLeft > 0 && (
                  <span className="text-amber-600 text-xs">
                    Expires in {formatTimeLeft(timeLeft)}
                  </span>
                )}
              </div>
              <div className="flex justify-between">
                <span>Still available for others:</span>
                {availableSeats > 0 ? (
                  <span className="font-semibold">{availableSeats}</span>
                ) : (
                  <span className="font-semibold text-amber-600">
                    Waitlist only
                  </span>
                )}
              </div>
            </>
          ) : (
            <div className="flex justify-between">
              <span>Available seats:</span>
              {availableSeats > 0 ? (
                <span className="font-semibold">{availableSeats}</span>
              ) : (
                <span className="font-semibold text-amber-600">
                  Waitlist only
                </span>
              )}
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}

export default EventDetails;
