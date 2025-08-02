import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { MapPin, Clock, ExternalLink } from "lucide-react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { SessionData } from "@/modules/eventRegistration/features/EventDirectory/api/fetchEventDetails/types";
import {
  formatDateRangeInTimeZone,
  formatTimeRangeInTimeZone,
  formatDateTimeRangeInTimeZone,
  formatLocalTimeRange,
  getLocalTimeZoneShortName,
} from "@/modules/eventRegistration/features/EventDirectory/utils/dateUtils";

function formatDateForICS(dateString: string) {
  const date = new Date(dateString);
  const year = date.getUTCFullYear();
  const month = String(date.getUTCMonth() + 1).padStart(2, "0");
  const day = String(date.getUTCDate()).padStart(2, "0");
  const hour = String(date.getUTCHours()).padStart(2, "0");
  const minute = String(date.getUTCMinutes()).padStart(2, "0");
  const second = String(date.getUTCSeconds()).padStart(2, "0");
  return `${year}${month}${day}T${hour}${minute}${second}Z`;
}

function createIcsContent(session: SessionData): string {
  const start = session.startDate
    ? formatDateForICS(session.startDate)
    : formatDateForICS(new Date().toISOString());
  const end = session.endDate ? formatDateForICS(session.endDate) : start;
  const summary = session.name || "Session";
  const description = `Event Session: ${session.name || "Session"}`;
  return [
    "BEGIN:VCALENDAR",
    "VERSION:2.0",
    "PRODID:-//ExampleCorp//NONSGML v1.0//EN",
    "BEGIN:VEVENT",
    `UID:${session.uuid || Date.now()}@example.com`,
    `DTSTAMP:${formatDateForICS(new Date().toISOString())}`,
    `DTSTART:${start}`,
    `DTEND:${end}`,
    `SUMMARY:${summary}`,
    `DESCRIPTION:${description}`,
    "END:VEVENT",
    "END:VCALENDAR",
  ].join("\r\n");
}

function handleAddToCalendar(session: SessionData) {
  const icsContent = createIcsContent(session);
  const blob = new Blob([icsContent], { type: "text/calendar;charset=utf-8" });
  const blobUrl = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = blobUrl;
  link.setAttribute("download", `${session.name || "event"}.ics`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(blobUrl);
}

function formatTimeLeft(seconds: number): string {
  if (seconds <= 0) {
    return "0:00";
  }
  const minutes = Math.floor(seconds / 60);
  const leftoverSeconds = seconds % 60;
  return `${minutes}:${leftoverSeconds.toString().padStart(2, "0")}`;
}

interface EventDetailsSessionListProps {
  sessions: SessionData[] | null | undefined;
}

const EventDetailsSessionList: React.FC<EventDetailsSessionListProps> = ({
  sessions,
}) => {
  const navigate = useNavigate();
  const localShortName = getLocalTimeZoneShortName();
  const [localSessions, setLocalSessions] = useState<SessionData[]>([]);

  useEffect(() => {
    if (sessions && sessions.length > 0) {
      setLocalSessions([...sessions]);
    } else {
      setLocalSessions([]);
    }
  }, [sessions]);

  // Decrement the time left every second
  useEffect(() => {
    const intervalId = setInterval(() => {
      setLocalSessions((prev) =>
        prev.map((session) => {
          if (
            session.timeLeftForCurrentUser &&
            session.timeLeftForCurrentUser > 0
          ) {
            return {
              ...session,
              timeLeftForCurrentUser: session.timeLeftForCurrentUser - 1,
            };
          }
          return session;
        }),
      );
    }, 1000);
    return () => clearInterval(intervalId);
  }, []);

  if (!localSessions || localSessions.length === 0) {
    return null;
  }

  function buildMapsUrl(
    address?: string | null,
    city?: string | null,
    state?: string | null,
    postalCode?: string | null,
  ): string {
    const fullAddress = [address, city, state, postalCode]
      .filter(Boolean)
      .join(", ");
    return `https://maps.google.com/?q=${encodeURIComponent(fullAddress)}`;
  }

  return (
    <div className="p-4 lg:p-6">
      <h2 className="text-xl font-semibold mb-4">Available Sessions</h2>

      {/* Desktop View */}
      <div className="hidden lg:block">
        <Table className="table-fixed w-full text-sm break-words">
          <TableHeader>
            <TableRow>
              <TableHead className="py-3 px-4">Session</TableHead>
              <TableHead className="py-3 px-4">Date & Time</TableHead>
              <TableHead className="py-3 px-4">Venue</TableHead>
              <TableHead className="py-3 px-4">Availability</TableHead>
              <TableHead className="py-3 px-4">Your Reservation</TableHead>
              <TableHead className="py-3 px-4 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {localSessions.map((session) => {
              const dateRange = formatDateRangeInTimeZone(
                session.startDate || null,
                session.endDate || null,
                session.timezoneIdentifier || null,
              );
              const timeRange = formatTimeRangeInTimeZone(
                session.startDate || null,
                session.endDate || null,
                session.timezoneIdentifier || null,
              );
              const mainTimeDisplay = session.timezoneShortName
                ? `${timeRange} (${session.timezoneShortName})`
                : timeRange;
              const localRange = formatLocalTimeRange(
                session.startDate || null,
                session.endDate || null,
              );
              const localTimeWithShortName = `${localRange} (${localShortName})`;
              const showLocalTime =
                !session.timezoneShortName ||
                session.timezoneShortName !== localShortName;

              let venueDisplay: JSX.Element | null = null;

              if (session.venue) {
                const mapsUrl = buildMapsUrl(
                  session.venue.address,
                  session.venue.city,
                  session.venue.state,
                  session.venue.postalCode,
                );
                venueDisplay = (
                  <div className="flex flex-col">
                    <span className="flex items-center gap-1 font-medium">
                      <MapPin className="shrink-0 h-4 w-4" />
                      {session.venue.name || "Unnamed Venue"}
                    </span>
                    {session.venue.address && (
                      <span className="text-xs text-gray-500 dark:text-gray-400">
                        <a
                          className="underline text-blue-600 dark:text-blue-400"
                          href={mapsUrl}
                          rel="noopener noreferrer"
                          target="_blank"
                        >
                          {session.venue.address}
                        </a>
                      </span>
                    )}
                    {session.venue.city && (
                      <span className="text-xs text-gray-500 dark:text-gray-400">
                        <a
                          className="underline text-blue-600 dark:text-blue-400"
                          href={mapsUrl}
                          rel="noopener noreferrer"
                          target="_blank"
                        >
                          {session.venue.city}, {session.venue.state}{" "}
                          {session.venue.postalCode}
                        </a>
                      </span>
                    )}
                  </div>
                );
              } else if (session.isVirtualOnly) {
                venueDisplay = (
                  <div className="flex items-center gap-1">
                    <MapPin className="shrink-0 h-4 w-4" />
                    <span className="text-sm">Virtual-Only</span>
                  </div>
                );
              } else {
                venueDisplay = (
                  <div className="flex items-center gap-1 text-sm italic">
                    <MapPin className="shrink-0 h-4 w-4" />
                    <span>Not specified</span>
                  </div>
                );
              }

              const seats = session.availableSeats;
              let availabilityEl: JSX.Element = (
                <Badge variant="outline">Unknown</Badge>
              );

              if (typeof seats === "number") {
                if (seats > 0) {
                  availabilityEl = (
                    <Badge
                      className="bg-green-100 text-green-800 border-green-200"
                      variant="outline"
                    >
                      {seats} seats available
                    </Badge>
                  );
                } else {
                  availabilityEl = (
                    <Badge
                      className="bg-amber-100 text-amber-800 border-amber-200"
                      variant="outline"
                    >
                      Waitlist only
                    </Badge>
                  );
                }
              }

              const userHeldSeats = session.occupiedAttendeeSeatsByCurrentUser;
              const timeLeft = session.timeLeftForCurrentUser || 0;

              return (
                <TableRow
                  className="hover:bg-gray-50 dark:hover:bg-gray-700"
                  key={session.id}
                >
                  <TableCell className="py-3 px-4 font-medium">
                    {session.name || "Session"}
                  </TableCell>
                  <TableCell className="py-3 px-4">
                    <div className="flex flex-col gap-1">
                      <span>{dateRange}</span>
                      <span className="text-xs text-gray-500">
                        {mainTimeDisplay}
                      </span>
                      {showLocalTime && (
                        <span className="text-xs text-gray-500">
                          (Local: {localTimeWithShortName})
                        </span>
                      )}
                      <Button
                        className="px-0 text-blue-600 dark:text-blue-400 justify-start"
                        onClick={() => handleAddToCalendar(session)}
                        size="sm"
                        variant="link"
                      >
                        + Add to Calendar
                      </Button>
                    </div>
                  </TableCell>
                  <TableCell className="py-3 px-4">{venueDisplay}</TableCell>
                  <TableCell className="py-3 px-4">{availabilityEl}</TableCell>
                  <TableCell className="py-3 px-4">
                    {userHeldSeats > 0 ? (
                      <>
                        <span>{userHeldSeats} seat(s) held</span>
                        {timeLeft > 0 && (
                          <span className="text-xs text-amber-600 block">
                            Expires in {formatTimeLeft(timeLeft)}
                          </span>
                        )}
                      </>
                    ) : (
                      <span className="text-xs text-gray-500">None</span>
                    )}
                  </TableCell>
                  <TableCell className="py-3 px-4 text-right">
                    <div className="flex justify-end gap-2">
                      <Button
                        onClick={() =>
                          navigate(
                            `/event-registration/events/register/${session.uuid}/entry`,
                          )
                        }
                        size="sm"
                        variant="default"
                      >
                        Register
                      </Button>
                      {session.virtualLink && (
                        <Button size="sm" variant="outline">
                          <a
                            className="flex items-center gap-1"
                            href={session.virtualLink}
                            rel="noopener noreferrer"
                            target="_blank"
                          >
                            <ExternalLink className="h-4 w-4 shrink-0" />
                            Join
                          </a>
                        </Button>
                      )}
                    </div>
                  </TableCell>
                </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </div>

      {/* Mobile View */}
      <div className="block lg:hidden space-y-4">
        {localSessions.map((session) => {
          const sessionTimezoneDisplay = formatDateTimeRangeInTimeZone(
            session.startDate || null,
            session.endDate || null,
            session.timezoneIdentifier || null,
          );
          const mainTimeDisplay = session.timezoneShortName
            ? `${sessionTimezoneDisplay} (${session.timezoneShortName})`
            : sessionTimezoneDisplay;
          const localDeviceTimeDisplay = formatLocalTimeRange(
            session.startDate || null,
            session.endDate || null,
          );
          const localTimeWithShortName = `${localDeviceTimeDisplay} (${localShortName})`;
          const showLocalTime =
            !session.timezoneShortName ||
            session.timezoneShortName !== localShortName;

          const mapsUrl = session.venue
            ? buildMapsUrl(
                session.venue.address,
                session.venue.city,
                session.venue.state,
                session.venue.postalCode,
              )
            : "";

          let venueDisplay: JSX.Element | null = null;

          if (session.venue) {
            venueDisplay = (
              <>
                <span className="flex items-center gap-1 font-medium">
                  <MapPin className="shrink-0 h-4 w-4" />
                  {session.venue.name || "Unnamed Venue"}
                </span>
                {session.venue.address && (
                  <span className="text-xs text-gray-500 dark:text-gray-400">
                    <a
                      className="underline text-blue-600 dark:text-blue-400"
                      href={mapsUrl}
                      rel="noopener noreferrer"
                      target="_blank"
                    >
                      {session.venue.address}
                    </a>
                  </span>
                )}
                {session.venue.city && (
                  <span className="text-xs text-gray-500 dark:text-gray-400">
                    <a
                      className="underline text-blue-600 dark:text-blue-400"
                      href={mapsUrl}
                      rel="noopener noreferrer"
                      target="_blank"
                    >
                      {session.venue.city}, {session.venue.state}{" "}
                      {session.venue.postalCode}
                    </a>
                  </span>
                )}
              </>
            );
          } else if (session.isVirtualOnly) {
            venueDisplay = (
              <span className="flex items-center gap-1">
                <MapPin className="shrink-0 h-4 w-4" />
                <span className="text-sm">Virtual-Only</span>
              </span>
            );
          } else {
            venueDisplay = (
              <span className="flex items-center gap-1 text-sm italic">
                <MapPin className="shrink-0 h-4 w-4" />
                <span>Not specified</span>
              </span>
            );
          }

          const seats = session.availableSeats;
          let availabilityEl: JSX.Element = (
            <Badge variant="outline">Unknown</Badge>
          );

          if (typeof seats === "number") {
            if (seats > 0) {
              availabilityEl = (
                <Badge
                  className="bg-green-100 text-green-800 border-green-200"
                  variant="outline"
                >
                  {seats} seats available
                </Badge>
              );
            } else {
              availabilityEl = (
                <Badge
                  className="bg-amber-100 text-amber-800 border-amber-200"
                  variant="outline"
                >
                  Waitlist only
                </Badge>
              );
            }
          }

          const userHeldSeats = session.occupiedAttendeeSeatsByCurrentUser;
          const timeLeft = session.timeLeftForCurrentUser || 0;

          return (
            <div
              className="p-4 border rounded-md flex flex-col gap-2 bg-white dark:bg-gray-800"
              key={session.id}
            >
              <div className="flex flex-col">
                <span className="text-base font-semibold">
                  {session.name || "Session"}
                </span>
              </div>
              <div className="flex flex-col">
                <span className="flex items-center gap-1">
                  <Clock className="shrink-0 h-4 w-4" />
                  {mainTimeDisplay}
                </span>
                {showLocalTime && (
                  <span className="text-xs text-gray-500">
                    (Local: {localTimeWithShortName})
                  </span>
                )}
              </div>
              <div className="flex items-start flex-col gap-1">
                <Button
                  className="px-0 text-blue-600 dark:text-blue-400"
                  onClick={() => handleAddToCalendar(session)}
                  size="sm"
                  variant="link"
                >
                  + Add to Calendar
                </Button>
              </div>
              <div>{venueDisplay}</div>
              <div>{availabilityEl}</div>
              <div className="flex flex-col gap-1 text-sm">
                <span className="font-medium">Your Reservation:</span>
                {userHeldSeats > 0 ? (
                  <>
                    <span>{userHeldSeats} seat(s) held</span>
                    {timeLeft > 0 && (
                      <span className="text-amber-600 text-xs">
                        Expires in {formatTimeLeft(timeLeft)}
                      </span>
                    )}
                  </>
                ) : (
                  <span className="text-gray-500 text-xs">None</span>
                )}
              </div>
              <div className="flex justify-end gap-2">
                <Button
                  onClick={() =>
                    navigate(
                      `/event-registration/events/register/${session.uuid}/entry`,
                    )
                  }
                  size="sm"
                  variant="default"
                >
                  Register
                </Button>
                {session.virtualLink && (
                  <Button size="sm" variant="outline">
                    <a
                      className="flex items-center gap-1"
                      href={session.virtualLink}
                      rel="noopener noreferrer"
                      target="_blank"
                    >
                      <ExternalLink className="h-4 w-4 shrink-0" />
                      Join
                    </a>
                  </Button>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default EventDetailsSessionList;
