import React, { useEffect, useState } from "react";
import { format } from "date-fns";
import {
  ArrowLeft,
  CheckCircle,
  Download,
  Calendar as CalendarIcon,
  MapPin,
} from "lucide-react";
import { useParams, useNavigate } from "react-router-dom";

import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";

import { GetEventCheckoutConfirmationDetailsResponseData } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutConfirmationDetails/types";
import { getEventCheckoutConfirmationDetails } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutConfirmationDetails/getEventCheckoutConfirmationDetailsApi";
import { getEventCheckoutConfirmationPdf } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutConfirmationPdf/getEventCheckoutConfirmationPdfApi";

import {
  formatDateTimeRangeInTimeZone,
  formatLocalTimeRange,
  getLocalTimeZoneShortName,
} from "@/modules/eventRegistration/features/EventDirectory/utils/dateUtils";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";

/**
 * Helper to build a Google Maps URL from address info
 */
function buildMapsUrl(
  address?: string | null,
  city?: string | null,
  state?: string | null,
  postalCode?: string | null,
): string {
  const parts = [address, city, state, postalCode].filter(Boolean).join(", ");
  return `https://www.google.com/maps?q=${encodeURIComponent(parts)}`;
}

/**
 * Helper: Convert "YYYY-MM-DDTHH:mm:ss" to ICS-friendly format: "YYYYMMDDTHHMMSSZ"
 * If date is missing or invalid, returns an empty string.
 */
function toICSDateString(isoDate?: string | null): string {
  if (!isoDate) return "";
  try {
    const date = new Date(isoDate);
    // Convert to yyyymmddTHHMMSSZ
    return date
      .toISOString()
      .replace(/[-:]/g, "")
      .replace(/\.\d{3}/, ""); // remove milliseconds
  } catch {
    return "";
  }
}

function Confirmation() {
  // Pull the UUID from the route params
  const { eventCheckoutSessionUuid } = useParams();
  const navigate = useNavigate();

  const [details, setDetails] =
    useState<GetEventCheckoutConfirmationDetailsResponseData | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  // Fetch data on mount
  useEffect(() => {
    window.scrollTo(0, 0);

    if (!eventCheckoutSessionUuid) {
      setError("No event checkout session uuid found in route.");
      return;
    }

    (async () => {
      try {
        setIsLoading(true);
        const response = await getEventCheckoutConfirmationDetails(
          eventCheckoutSessionUuid,
        );
        setDetails(response.data);
      } catch {
        setError(
          "Failed to load registration details. Please try again later.",
        );
      } finally {
        setIsLoading(false);
      }
    })();
  }, [eventCheckoutSessionUuid]);

  /**
   * Download the PDF of the user’s confirmation.
   */
  async function handleDownloadConfirmation() {
    if (!details || !eventCheckoutSessionUuid) return;
    try {
      const { data: pdfBlob } = await getEventCheckoutConfirmationPdf(
        eventCheckoutSessionUuid,
      );

      // Create a blob URL and trigger download
      const url = window.URL.createObjectURL(pdfBlob);
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute(
        "download",
        `confirmation-${details.confirmationNumber || "unknown"}.pdf`,
      );
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch {
      alert("Failed to download PDF");
    }
  }

  /**
   * Creates and downloads an ICS file for the event session.
   */
  function handleAddToCalendar() {
    if (!details) return;

    // Convert start/end to ICS date-time
    const startIcs = toICSDateString(details.startDate);
    const endIcs = toICSDateString(details.endDate);

    // Minimal ICS text with fields: DTSTART, DTEND, SUMMARY
    // You may add more ICS fields if desired, like LOCATION, DESCRIPTION, etc.
    const icsContent = [
      "BEGIN:VCALENDAR",
      "VERSION:2.0",
      "BEGIN:VEVENT",
      startIcs ? `DTSTART:${startIcs}Z` : "",
      endIcs ? `DTEND:${endIcs}Z` : "",
      `SUMMARY:${details.eventName ?? "Event"}`,
      "END:VEVENT",
      "END:VCALENDAR",
      "",
    ].join("\r\n");

    // Create a temporary .ics file in memory
    const fileBlob = new Blob([icsContent], { type: "text/calendar" });
    const fileUrl = window.URL.createObjectURL(fileBlob);
    const link = document.createElement("a");
    link.href = fileUrl;
    link.setAttribute("download", "event.ics");
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(fileUrl);
  }

  function handleGoBack() {
    // You could navigate to the prior screen or a specific route
    navigate("/event-registration/events");
  }

  // Loading state
  if (isLoading) {
    return (
      <div className="container mx-auto py-8">
        <div className="max-w-4xl mx-auto">
          <Skeleton className="h-8 w-64 mb-6" />
          <Card>
            <CardHeader>
              <Skeleton className="h-7 w-48 mb-2" />
              <Skeleton className="h-4 w-full max-w-md" />
            </CardHeader>
            <CardContent className="space-y-6">
              <Skeleton className="h-32 w-full" />
              <Skeleton className="h-24 w-full" />
            </CardContent>
            <CardFooter>
              <Skeleton className="h-10 w-full max-w-xs" />
            </CardFooter>
          </Card>
        </div>
      </div>
    );
  }

  // Error state
  if (error) {
    return (
      <div className="container mx-auto py-8">
        <div className="max-w-4xl mx-auto">
          <Alert variant="destructive">
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{error}</AlertDescription>
          </Alert>
          <div className="mt-6">
            <Button onClick={handleGoBack}>
              <ArrowLeft className="h-4 w-4 mr-2" />
              Return to Events
            </Button>
          </div>
        </div>
      </div>
    );
  }

  // If fetch finished but data is null, show a fallback
  if (!details) {
    return (
      <div className="container mx-auto py-8">
        <div className="max-w-4xl mx-auto">
          <p>No confirmation details found.</p>
        </div>
      </div>
    );
  }

  // Pull data from the response
  const {
    confirmationNumber,
    finalizedAt,
    amount,
    contactEmail,
    eventName,
    eventSessionName,
    startDate,
    endDate,
    isVirtualOnly,
    venueName,
    venueAddress,
    venueCity,
    venueState,
    venuePostalCode,
    venueCountry,
    timezoneIdentifier,
    timezoneShortName,
  } = details;

  // Format date/time for "registration date"
  const registrationDateDisplay = finalizedAt
    ? format(new Date(finalizedAt), "MMMM d, yyyy h:mm a")
    : "N/A";

  // Build local/timezone aware strings for the event's start/end
  const sessionTimezoneRange = formatDateTimeRangeInTimeZone(
    startDate,
    endDate,
    timezoneIdentifier,
  );
  const localTimeRange = formatLocalTimeRange(startDate, endDate);
  const localShortName = getLocalTimeZoneShortName();

  // Decide how to display the venue (with Google Maps link if any address data)
  let venueBlock = null;
  if (isVirtualOnly) {
    venueBlock = (
      <div>
        <p className="text-muted-foreground">Venue</p>
        <div className="flex gap-2 items-center">
          <MapPin className="h-4 w-4" />
          <span className="italic">Virtual-only</span>
        </div>
      </div>
    );
  } else if (venueName || venueAddress || venueCity) {
    const mapUrl = buildMapsUrl(
      venueAddress,
      venueCity,
      venueState,
      venuePostalCode,
    );

    venueBlock = (
      <div>
        <p className="text-muted-foreground">Venue</p>
        <div className="flex flex-col gap-1">
          <div className="flex items-center gap-2">
            <MapPin className="h-4 w-4" />
            <span>{venueName || "Unnamed Venue"}</span>
          </div>
          {venueAddress && (
            <a
              className="ml-6 underline text-blue-600 dark:text-blue-400 text-sm"
              href={mapUrl}
              rel="noopener noreferrer"
              target="_blank"
            >
              {venueAddress}
            </a>
          )}
          {venueCity && (
            <a
              className="ml-6 underline text-blue-600 dark:text-blue-400 text-sm"
              href={mapUrl}
              rel="noopener noreferrer"
              target="_blank"
            >
              {venueCity}
              {venueState ? `, ${venueState}` : ""}
              {venuePostalCode ? ` ${venuePostalCode}` : ""}
            </a>
          )}
          {venueCountry && (
            <span className="ml-6 text-sm text-muted-foreground">
              {venueCountry}
            </span>
          )}
        </div>
      </div>
    );
  } else {
    venueBlock = (
      <div>
        <p className="text-muted-foreground">Venue</p>
        <div className="flex gap-2 items-center">
          <MapPin className="h-4 w-4" />
          <span className="italic">Not specified</span>
        </div>
      </div>
    );
  }

  return (
    <MainPageWrapper hideHeader title="Confirmation">
      <div>
        <div className="max-w-4xl mx-auto">
          <div className="flex items-center justify-between mb-6">
            <Button onClick={handleGoBack} variant="ghost">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Return to Events
            </Button>
            <h1 className="text-2xl font-bold hidden md:block">
              Registration Confirmation
            </h1>
          </div>
          <h1 className="text-2xl font-bold mb-6 md:hidden">
            Registration Confirmation
          </h1>

          <Card className="mb-8">
            <CardHeader className="bg-primary/5 border-b">
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <CheckCircle className="h-6 w-6 text-green-500 mr-3" />
                  <CardTitle>Registration Complete</CardTitle>
                </div>
                <Badge
                  className="text-green-500 border-green-500"
                  variant="outline"
                >
                  Confirmed
                </Badge>
              </div>
            </CardHeader>
            <CardContent className="pt-6 pb-4">
              <div className="space-y-6">
                {/* Basic event info */}
                <div>
                  <h2 className="text-xl font-semibold mb-2">
                    {eventName || "Event Registration"}
                  </h2>
                  <p className="text-muted-foreground">
                    Your registration has been successfully processed.
                  </p>
                </div>
                <Separator />
                {/* Confirmation details */}
                <div className="space-y-4">
                  <h3 className="font-medium">Confirmation Details</h3>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                      <p className="text-muted-foreground">
                        Confirmation Number
                      </p>
                      <p className="font-medium">
                        {confirmationNumber || "N/A"}
                      </p>
                    </div>
                    <div>
                      <p className="text-muted-foreground">Registration Date</p>
                      <p>{registrationDateDisplay}</p>
                    </div>
                    <div>
                      <p className="text-muted-foreground">Status</p>
                      <p className="font-medium text-green-500">Confirmed</p>
                    </div>
                    <div>
                      <p className="text-muted-foreground">Payment Status</p>
                      <p className="font-medium">Paid</p>
                    </div>
                    {eventSessionName && (
                      <div>
                        <p className="text-muted-foreground">Session</p>
                        <p className="font-medium">{eventSessionName}</p>
                      </div>
                    )}
                    <div>
                      <p className="text-muted-foreground">Amount</p>
                      <p>${amount || "0.00"}</p>
                    </div>

                    {/* Show the event's date/time with the official timezone & local time */}
                    {(startDate || endDate) && (
                      <div className="md:col-span-2">
                        <p className="text-muted-foreground">Event Timing</p>
                        <p className="font-medium">
                          {timezoneShortName || timezoneIdentifier
                            ? `In ${timezoneShortName || timezoneIdentifier}: ${sessionTimezoneRange}`
                            : sessionTimezoneRange || "N/A"}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          Local: {localTimeRange} ({localShortName})
                        </p>
                      </div>
                    )}

                    {/* Venue block (maps link / address) */}
                    <div className="md:col-span-2">{venueBlock}</div>
                  </div>
                </div>
                <Separator />
                {/* Next steps */}
                <div className="space-y-4">
                  <h3 className="font-medium">Next Steps</h3>
                  <p>
                    A confirmation email has been sent to{" "}
                    {contactEmail || "your email"} with all the details about
                    your registration.
                  </p>
                  <p>
                    If you have any questions or need to make changes to your
                    registration, please contact our support team.
                  </p>
                </div>
              </div>
            </CardContent>
            <CardFooter className="flex flex-wrap gap-3 pt-2 pb-6">
              <Button
                className="flex-1 sm:flex-none"
                onClick={handleDownloadConfirmation}
              >
                <Download className="h-4 w-4 mr-2" />
                Download
              </Button>
              <Button
                className="flex-1 sm:flex-none"
                onClick={handleAddToCalendar}
                variant="outline"
              >
                <CalendarIcon className="h-4 w-4 mr-2" />
                Add to Calendar
              </Button>
            </CardFooter>
          </Card>
        </div>
      </div>
    </MainPageWrapper>
  );
}

export default Confirmation;
