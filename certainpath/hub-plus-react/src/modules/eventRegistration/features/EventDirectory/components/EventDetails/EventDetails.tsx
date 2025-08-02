import React, { useEffect, useMemo } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import {
  fetchEventDetailsAction,
  toggleFavoriteEventAction,
} from "../../slices/eventDirectorySlice";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import {
  ArrowLeft,
  Eye,
  Star,
  Calendar,
  DollarSign,
  FileText,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { updateEventViewCount } from "@/modules/eventRegistration/features/EventDirectory/api/updateEventViewCount/updateEventViewCountApi";
import EventDetailsSkeleton from "@/modules/eventRegistration/features/EventDirectory/components/EventDetailsSkeleton/EventDetailsSkeleton";
import { Button } from "@/components/ui/button";
import { downloadFile } from "@/api/downloadFile/downloadFileApi";
import EventDetailsSessionList from "@/modules/eventRegistration/features/EventDirectory/components/EventDetailsSessionList/EventDetailsSessionList";

function formatLocalDate(dateString: string | undefined): string {
  if (!dateString) return "--";
  return new Date(dateString).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function EventDetails(): JSX.Element {
  const { uuid } = useParams<{ uuid: string }>();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { eventDetails, fetchDetailsLoading, fetchDetailsError } =
    useAppSelector((state: RootState) => state.eventDirectory);

  const manualBreadcrumbs = useMemo(() => {
    if (!uuid) return undefined;
    const titleOrFallback = eventDetails?.eventName || `Event ${uuid}`;
    return [
      { path: "/event-registration/events", label: "Event Directory" },
      {
        path: "",
        label: `Event Details (${titleOrFallback})`,
        clickable: false,
      },
    ];
  }, [uuid, eventDetails?.eventName]);

  useEffect(() => {
    if (eventDetails && !fetchDetailsLoading) {
      const timer = setTimeout(() => {
        if (eventDetails.uuid) {
          updateEventViewCount({ uuid: eventDetails.uuid }).catch((error) =>
            console.error("Error incrementing view count:", error),
          );
        }
      }, 1000);
      return () => clearTimeout(timer);
    }
  }, [eventDetails, fetchDetailsLoading]);

  useEffect(() => {
    if (!uuid) return;
    dispatch(fetchEventDetailsAction(uuid));
  }, [dispatch, uuid]);

  const handleToggleFavorite = () => {
    if (eventDetails?.uuid) {
      dispatch(toggleFavoriteEventAction(eventDetails.uuid));
    }
  };

  const handleFileDownload = async (
    fileUuid: string,
    originalFileName?: string,
  ) => {
    try {
      const blob = await downloadFile({ fileUuid });
      const blobUrl = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = blobUrl;
      link.setAttribute("download", originalFileName || "file");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(blobUrl);
    } catch (error) {
      console.error("File download failed:", error);
    }
  };

  if (fetchDetailsLoading) {
    return (
      <MainPageWrapper hideHeader title="Event Directory">
        <EventDetailsSkeleton />
      </MainPageWrapper>
    );
  }

  if (fetchDetailsError || !eventDetails || !eventDetails.uuid) {
    return (
      <MainPageWrapper hideHeader title="Event Directory">
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center my-8">
          <h1 className="text-2xl font-bold mb-4">Event Not Found</h1>
          <p className="mb-6">
            The event you’re looking for doesn’t exist or has been removed.
          </p>
          <button
            className="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline"
            onClick={() => navigate("/hub/event-directory")}
          >
            <ArrowLeft className="h-5 w-5" />
            Back to Events
          </button>
        </div>
      </MainPageWrapper>
    );
  }

  const {
    eventName,
    eventDescription,
    eventPrice,
    eventTypeName,
    eventCategoryName,
    thumbnailUrl,
    viewCount,
    createdAt,
    updatedAt,
    trades,
    roles,
    sessions,
    files,
    isFavorited,
  } = eventDetails;
  const priceDisplay =
    eventPrice && eventPrice > 0 ? `$${eventPrice.toFixed(2)}` : "Free";
  const dateCreated = formatLocalDate(createdAt || "");
  const dateUpdated = updatedAt ? formatLocalDate(updatedAt) : null;

  return (
    <MainPageWrapper
      hideHeader
      manualBreadcrumbs={manualBreadcrumbs}
      title="Event Details"
    >
      <div className="mb-6 pl-5">
        <Button
          className="px-0 flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
          onClick={() => navigate("/event-registration/events")}
          variant="link"
        >
          <ArrowLeft size={16} />
          Back to Events
        </Button>
      </div>
      <div className="space-y-6">
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
          <div className="flex flex-col lg:flex-row gap-6 items-start p-4 lg:p-6">
            <div className="relative w-full lg:w-48 h-48 shrink-0 rounded-lg overflow-hidden">
              {thumbnailUrl ? (
                <img
                  alt={eventName || "Event Thumbnail"}
                  className="object-cover w-full h-full"
                  src={thumbnailUrl}
                />
              ) : (
                <div className="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                  <span className="text-sm text-gray-500">No Image</span>
                </div>
              )}
            </div>
            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-4 mb-3">
                <h1 className="text-2xl lg:text-3xl font-bold">{eventName}</h1>
                <button
                  aria-label="Toggle Favorite"
                  className="inline-flex items-center gap-1 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                  onClick={handleToggleFavorite}
                >
                  <Star
                    className={`h-5 w-5 ${
                      isFavorited ? "text-yellow-500 fill-yellow-500" : ""
                    }`}
                  />
                </button>
              </div>
              <div className="flex flex-wrap items-center gap-4 text-sm mb-3">
                {eventTypeName && (
                  <Badge className="text-white" variant="default">
                    {eventTypeName}
                  </Badge>
                )}
                {eventCategoryName && (
                  <Badge className="text-white" variant="default">
                    {eventCategoryName}
                  </Badge>
                )}
                {!!trades?.length &&
                  trades.map((trade) => (
                    <Badge
                      className="text-white"
                      key={trade.id}
                      variant="default"
                    >
                      {trade.name}
                    </Badge>
                  ))}
                {!!roles?.length &&
                  roles.map((role) => (
                    <Badge
                      className="text-white"
                      key={role.id}
                      variant="default"
                    >
                      {role.name}
                    </Badge>
                  ))}
                <span className="flex items-center gap-1">
                  <Eye size={16} />
                  {viewCount || 0} views
                </span>
                <span className="flex items-center gap-1">
                  <DollarSign size={16} />
                  {priceDisplay}
                </span>
              </div>
              <div className="flex flex-col lg:flex-row lg:items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                <div className="flex items-center gap-1">
                  <Calendar size={16} />
                  Created: {dateCreated}
                </div>
                {dateUpdated && (
                  <div className="flex items-center gap-1">
                    <Calendar size={16} />
                    Updated: {dateUpdated}
                  </div>
                )}
              </div>
              {!!eventDescription && (
                <p className="prose dark:prose-invert max-w-none whitespace-pre-wrap mt-4">
                  {eventDescription}
                </p>
              )}
            </div>
          </div>
          <EventDetailsSessionList sessions={sessions} />
          {files && files.length > 0 && (
            <div className="p-4 lg:p-6">
              <h2 className="text-xl font-semibold mb-3">Attached Files</h2>
              <div className="space-y-2">
                {files.map((file) => (
                  <div
                    className="p-3 border rounded-lg flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-600"
                    key={file.id}
                  >
                    <div className="flex items-center gap-2 truncate">
                      <FileText className="h-5 w-5 text-gray-400" />
                      <span className="text-sm text-gray-600 dark:text-gray-300 truncate">
                        {file.originalFileName || `File #${file.id}`}
                      </span>
                    </div>
                    <Button
                      className="text-blue-600 dark:text-blue-400 hover:underline text-sm px-0"
                      onClick={() =>
                        handleFileDownload(file.uuid, file.originalFileName)
                      }
                      variant="link"
                    >
                      Download
                    </Button>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </MainPageWrapper>
  );
}

export default EventDetails;
