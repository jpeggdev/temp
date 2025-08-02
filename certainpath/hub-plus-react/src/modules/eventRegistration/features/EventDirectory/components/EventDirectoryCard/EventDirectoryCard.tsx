import React from "react";
import { Link } from "react-router-dom";
import { formatDistance } from "date-fns";
import { Eye, Ticket } from "lucide-react";
import { SearchResultEvent } from "../../api/fetchEventSearchResults/types";

interface EventDirectoryCardProps {
  event: SearchResultEvent;
}

export function EventDirectoryCard({ event }: EventDirectoryCardProps) {
  const DESCRIPTION_MAX_LENGTH = 120;

  const truncatedDescription = event.eventDescription
    ? event.eventDescription.length > DESCRIPTION_MAX_LENGTH
      ? event.eventDescription.substring(0, DESCRIPTION_MAX_LENGTH) + "..."
      : event.eventDescription
    : "";

  let timeAgo: string | null = null;
  if (event.createdAt) {
    const createdDate = new Date(event.createdAt);
    const diffMs = Date.now() - createdDate.getTime();
    if (diffMs < 60000) {
      timeAgo = "1 minute ago";
    } else {
      timeAgo = formatDistance(createdDate, new Date(), { addSuffix: true });
    }
  }

  const eventLink = `/event-registration/events/${event.uuid}`;

  return (
    <div className="block group rounded-lg shadow-sm hover:shadow-md transition-all bg-white dark:bg-gray-800">
      <Link className="block" to={eventLink}>
        <div className="relative aspect-video">
          {event.thumbnailUrl ? (
            <img
              alt={event.eventName}
              className="object-cover rounded-t-lg w-full h-full"
              src={event.thumbnailUrl}
            />
          ) : (
            <div className="w-full h-full rounded-t-lg flex items-center justify-center bg-gray-100 dark:bg-gray-700">
              <span className="text-gray-500 text-sm">No Image</span>
            </div>
          )}

          {event.isVoucherEligible && (
            <div className="absolute bottom-2 right-2 z-10">
              <span className="text-xs font-medium px-2 py-1 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-100 flex items-center gap-1 shadow-sm">
                <Ticket className="h-3 w-3" />
                Vouchers Accepted
              </span>
            </div>
          )}
        </div>

        <div className="p-4">
          {event.eventTypeName && (
            <div className="flex items-center gap-2 mb-2">
              <span className="text-xs font-medium px-2 py-1 rounded-full bg-primary text-white">
                {event.eventTypeName}
              </span>
            </div>
          )}

          <h3 className="font-semibold text-base mb-1">{event.eventName}</h3>

          {truncatedDescription && (
            <p className="mt-1 text-sm text-gray-700 dark:text-gray-200">
              {truncatedDescription}
            </p>
          )}

          {event.eventPrice > 0 ? (
            <p className="mt-2 text-sm font-medium text-gray-800 dark:text-gray-100">
              ${event.eventPrice.toFixed(2)}
            </p>
          ) : (
            <p className="mt-2 text-sm font-medium text-green-600">Free</p>
          )}

          <div className="mt-4 flex items-center justify-between text-sm gap-2 text-gray-500 dark:text-gray-400">
            <span className="flex items-center gap-1">
              <Eye className="h-4 w-4" />
              {event.viewCount || 0} views
            </span>
            {timeAgo && <time>{timeAgo}</time>}
          </div>
        </div>
      </Link>
    </div>
  );
}
