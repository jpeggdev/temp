import React from "react";
import { Link } from "react-router-dom";
import { formatDistance } from "date-fns";
import { Eye } from "lucide-react";
import { GetResourceSearchResultsItem } from "@/api/getResourceSearchResults/types";
import { useAppDispatch } from "@/app/hooks";
import { setScrollPosition } from "@/modules/hub/features/ResourceLibrary/slices/resourceLibrarySlice";

interface ResourceCardProps {
  resource: GetResourceSearchResultsItem;
}

export function ResourceCard({ resource }: ResourceCardProps) {
  const dispatch = useAppDispatch();

  const DESCRIPTION_MAX_LENGTH = 120;
  const contentType = (resource.resourceType || "document").toLowerCase();

  const handleCardClick = () => {
    const scrollPosition =
      window.scrollY ||
      window.pageYOffset ||
      document.documentElement.scrollTop ||
      document.body.scrollTop;

    dispatch(setScrollPosition(scrollPosition));
  };

  const truncatedDescription = resource.description
    ? resource.description.length > DESCRIPTION_MAX_LENGTH
      ? resource.description.substring(0, DESCRIPTION_MAX_LENGTH) + "..."
      : resource.description
    : "";

  let timeAgo: string | null = null;
  if (resource.createdOrPublishStartDate) {
    const createdDate = new Date(resource.createdOrPublishStartDate);
    const diffMs = Date.now() - createdDate.getTime();
    if (diffMs < 60000) {
      timeAgo = "1 minute ago";
    } else {
      timeAgo = formatDistance(createdDate, new Date(), { addSuffix: true });
    }
  }

  return (
    <div className="block group rounded-lg shadow-sm hover:shadow-md transition-all bg-white dark:bg-gray-800">
      <Link
        className="block"
        onClick={handleCardClick}
        to={`/hub/resources/${resource.slug}`}
      >
        <div className="relative aspect-video">
          {resource.thumbnailUrl ? (
            <img
              alt={resource.title}
              className="object-cover rounded-t-lg w-full h-full"
              src={resource.thumbnailUrl}
            />
          ) : (
            <div className="w-full h-full rounded-t-lg flex items-center justify-center bg-gray-100 dark:bg-gray-700">
              <span
                className="inline-block"
                dangerouslySetInnerHTML={{ __html: resource.primaryIcon ?? "" }}
              />
            </div>
          )}
        </div>

        <div className="p-4">
          <div className="flex items-center gap-2 mb-2">
            <span
              className="text-xs font-medium px-2 py-1 rounded-full"
              style={{
                backgroundColor: resource.backgroundColor ?? "#ccc",
                color: resource.textColor ?? "#000",
                border: resource.borderColor
                  ? `1px solid ${resource.borderColor}`
                  : "none",
              }}
            >
              {contentType.charAt(0).toUpperCase() + contentType.slice(1)}
            </span>
          </div>

          <h3 className="font-semibold">{resource.title}</h3>

          {truncatedDescription && (
            <p className="mt-2 text-sm">{truncatedDescription}</p>
          )}

          <div className="mt-4 flex items-center justify-between text-sm gap-2">
            <span className="flex items-center gap-1">
              <Eye className="h-4 w-4" />
              {resource.viewCount} views
            </span>
            {timeAgo && <time>{timeAgo}</time>}
          </div>
        </div>
      </Link>
    </div>
  );
}
