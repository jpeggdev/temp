import React, { useEffect, useState } from "react";
import { useParams, useNavigate, Link } from "react-router-dom";
import { ArrowLeft, Calendar, Eye } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import ResourceDetailsContentBlock from "@/modules/hub/features/ResourceLibrary/components/ResourceDetailsContentBlock/ResourceDetailsContentBlock";
import ResourceDetailsSkeleton from "../ResourceDetailsSkeleton/ResourceDetailsSkeleton";
import { FavoriteButton } from "@/modules/hub/features/ResourceLibrary/components/FavoriteButton/FavoriteButton";
import { ShareButton } from "@/modules/hub/features/ResourceLibrary/components/ShareButton/ShareButton";
import { updateResourceViewCount } from "@/api/updateResourceViewCount/updateResourceViewCountApi";
import { toggleResourceFavorite } from "@/api/favoriteResource/favoriteResourceApi";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { getResourceDetails } from "@/modules/hub/features/ResourceLibrary/api/getResourceDetails/getResourceDetailsApi";
import { GetResourceDetailsResponse } from "@/modules/hub/features/ResourceLibrary/api/getResourceDetails/types";
import { VideoEmbed } from "@/modules/hub/features/ResourceLibrary/components/VideoEmbed/VideoEmbed";
import { FileEmbed } from "@/modules/hub/features/ResourceLibrary/components/FileEmbed/FileEmbed";
import { PodcastEmbed } from "@/modules/hub/features/ResourceLibrary/components/PodcastEmbed/PodcastEmbed";

export default function ResourceDetails() {
  const { slug } = useParams<{ slug: string }>();
  const navigate = useNavigate();

  const [loading, setLoading] = useState<boolean>(true);
  const [resourceNotFound, setResourceNotFound] = useState<boolean>(false);
  const [resource, setResource] = useState<
    GetResourceDetailsResponse["data"] | null
  >(null);
  const [isFavorited, setIsFavorited] = useState<boolean>(false);

  const handleBackToResources = () => {
    navigate("/hub/resources");
  };

  useEffect(() => {
    async function fetchData() {
      if (!slug) return;
      setLoading(true);
      setResourceNotFound(false);

      try {
        const response = await getResourceDetails(slug);
        const resourceData = response.data;

        if (!resourceData || resourceData.id === null) {
          setResourceNotFound(true);
        } else {
          setResource(resourceData);
          setIsFavorited(resourceData.isFavorited);
        }
      } catch (error) {
        console.error("Failed to fetch resource:", error);
        setResourceNotFound(true);
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, [slug]);

  const incrementViewCount = async () => {
    if (!resource?.uuid) return;
    try {
      await updateResourceViewCount({ resourceUuid: resource.uuid });
    } catch (error) {
      console.error("Error incrementing view count:", error);
    }
  };

  useEffect(() => {
    if (resource && !loading) {
      const timer = setTimeout(() => {
        incrementViewCount();
      }, 1000);
      return () => clearTimeout(timer);
    }
  }, [resource, loading]);

  const handleToggleFavorite = async () => {
    if (!resource?.uuid) return;
    try {
      const response = await toggleResourceFavorite({
        resourceUuid: resource.uuid,
      });
      setIsFavorited(response.data.favorited);
    } catch (error) {
      console.error("Error toggling favorite:", error);
    }
  };

  if (resourceNotFound) {
    return (
      <MainPageWrapper hideHeader title="Resource Library">
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
          <h1 className="text-2xl font-bold mb-4">Resource Not Found</h1>
          <p className="mb-6">
            The resource you&apos;re looking for doesn&apos;t exist or has been
            removed.
          </p>
          <Button onClick={handleBackToResources}>
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Resources
          </Button>
        </div>
      </MainPageWrapper>
    );
  }

  if (loading) {
    return (
      <MainPageWrapper hideHeader title="Resource Library">
        <ResourceDetailsSkeleton />
      </MainPageWrapper>
    );
  }

  if (!resource) {
    return (
      <MainPageWrapper hideHeader title="Resource Library">
        <div className="max-w-6xl mx-auto">
          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
            <h1 className="text-2xl font-bold mb-4">Resource Not Found</h1>
            <p className="mb-6">
              The resource you&apos;re looking for doesn&apos;t exist or has
              been removed.
            </p>
            <Button onClick={handleBackToResources}>
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Resources
            </Button>
          </div>
        </div>
      </MainPageWrapper>
    );
  }

  const {
    title,
    tagline,
    description,
    thumbnailUrl,
    contentUrl,
    typeName,
    viewCount,
    publishStartDate,
    categories,
    trades,
    roles,
    tags,
    contentBlocks,
    icon,
    createdAt,
    updatedAt,
    backgroundColor,
    textColor,
    borderColor,
    primaryIcon,
    relatedResources,
    filename,
  } = resource;

  const manualBreadcrumbs = [
    { path: "/hub", label: "Hub Dashboard" },
    { path: "/hub/resources", label: "Resource Library" },
    { path: "/hub/resources", label: title ?? "", clickable: false },
  ];

  return (
    <MainPageWrapper
      hideHeader
      manualBreadcrumbs={manualBreadcrumbs}
      title="Resource Details"
    >
      <div>
        <div className="mb-6">
          <Button
            className="px-0 flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={handleBackToResources}
            variant="link"
          >
            <ArrowLeft size={16} />
            Back to Resources
          </Button>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
              <div className="flex flex-col md:flex-row gap-6 items-start">
                <div className="relative w-full md:w-48 h-48 shrink-0 rounded-lg overflow-hidden">
                  {thumbnailUrl ? (
                    <img
                      alt={title || "Resource Thumbnail"}
                      className="object-cover w-full h-full"
                      src={thumbnailUrl}
                    />
                  ) : (
                    <div className="w-full h-full rounded-t-lg flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                      <span
                        className="inline-block"
                        dangerouslySetInnerHTML={{
                          __html: primaryIcon ?? "",
                        }}
                      />
                    </div>
                  )}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex flex-wrap items-center justify-between gap-4 mb-3">
                    <h1 className="text-2xl md:text-3xl font-bold">{title}</h1>
                    <div className="flex items-center gap-2">
                      <FavoriteButton
                        isFavorited={isFavorited}
                        onToggle={handleToggleFavorite}
                      />
                      <ShareButton
                        description={tagline || "Check out this resource"}
                        title={title || "Resource"}
                        url={`/resources/${resource.slug}`}
                      />
                    </div>
                  </div>
                  {tagline && <p className="text-lg mb-3">{tagline}</p>}
                  <div className="flex flex-wrap items-center gap-4 text-sm mb-3">
                    <div
                      className="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full"
                      style={{
                        backgroundColor: backgroundColor ?? "#ccc",
                        color: textColor ?? "#000",
                        border: borderColor
                          ? `1px solid ${borderColor}`
                          : "none",
                      }}
                    >
                      <span dangerouslySetInnerHTML={{ __html: icon ?? "" }} />
                      <span>{typeName}</span>
                    </div>
                    <span className="flex items-center gap-1">
                      <Eye size={16} />
                      {viewCount || 0} views
                    </span>
                    {publishStartDate && (
                      <span className="flex items-center gap-1">
                        <Calendar size={16} />
                        {new Date(publishStartDate).toLocaleDateString()}
                      </span>
                    )}
                  </div>
                  <div className="mt-2 space-y-2">
                    {categories && categories.length > 0 && (
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="text-xs font-medium">Categories:</span>
                        <div className="flex flex-wrap gap-2">
                          {categories.map((category) => (
                            <Badge
                              className="text-xs"
                              key={category.id}
                              variant="outline"
                            >
                              {category.name}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}
                    {trades && trades.length > 0 && (
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="text-xs font-medium">Trades:</span>
                        <div className="flex flex-wrap gap-2">
                          {trades.map((trade) => (
                            <Badge
                              className="text-xs"
                              key={trade.id}
                              variant="secondary"
                            >
                              {trade.name}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}
                    {roles && roles.length > 0 && (
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="text-xs font-medium">Job Title:</span>
                        <div className="flex flex-wrap gap-2">
                          {roles.map((role) => (
                            <Badge
                              className="text-xs bg-blue-100 hover:bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                              key={role.id}
                              variant="secondary"
                            >
                              {role.name}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
              {description && (
                <div className="prose dark:prose-invert max-w-none mt-4 mb-8">
                  <p className="whitespace-pre-wrap">{description}</p>
                </div>
              )}

              <div className="mt-8">
                {typeName === "Video" && contentUrl && (
                  <div className="mb-8">
                    <div className="relative aspect-video rounded-lg overflow-hidden bg-black">
                      <VideoEmbed contentUrl={contentUrl} />
                    </div>
                  </div>
                )}

                {typeName === "Podcast" && contentUrl && (
                  <div className="mb-8">
                    <PodcastEmbed contentUrl={contentUrl} />
                  </div>
                )}

                {typeName === "Document" && contentUrl && (
                  <div className="mb-8">
                    <FileEmbed contentUrl={contentUrl} title={filename} />
                  </div>
                )}

                {contentBlocks && contentBlocks.length > 0 ? (
                  <div className="space-y-6">
                    {contentBlocks.map((block, index) => (
                      <ResourceDetailsContentBlock
                        block={block}
                        key={block.id || index}
                      />
                    ))}
                  </div>
                ) : (
                  !contentUrl && (
                    <div className="text-center py-10 text-gray-500 dark:text-gray-400">
                      <p>No detailed content available for this resource.</p>
                    </div>
                  )
                )}
              </div>
            </div>
          </div>

          <div className="space-y-6">
            <Card>
              <CardContent className="pt-6">
                <h3 className="text-lg font-semibold mb-4">Resource Details</h3>
                <div className="space-y-4">
                  {tags && tags.length > 0 && (
                    <div>
                      <h4 className="text-sm font-medium mb-2 flex items-center gap-2">
                        Tags
                      </h4>
                      <div className="flex flex-wrap gap-2">
                        {tags.map((tag) => (
                          <Badge
                            className="text-xs"
                            key={tag.id}
                            variant="outline"
                          >
                            {tag.name}
                          </Badge>
                        ))}
                      </div>
                    </div>
                  )}

                  <Separator />

                  <div>
                    <h4 className="text-sm font-medium mb-2">Information</h4>
                    <ul className="space-y-2 text-sm">
                      <li className="flex justify-between">
                        <span>Created</span>
                        <span className="font-medium">
                          {createdAt
                            ? new Date(createdAt).toLocaleDateString()
                            : "—"}
                        </span>
                      </li>
                      {updatedAt && (
                        <li className="flex justify-between">
                          <span>Updated</span>
                          <span className="font-medium">
                            {new Date(updatedAt).toLocaleDateString()}
                          </span>
                        </li>
                      )}
                    </ul>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="pt-6">
                <h3 className="text-lg font-semibold mb-4">
                  Recommended Resources
                </h3>
                {relatedResources && relatedResources.length > 0 ? (
                  <div className="space-y-4">
                    {relatedResources.map((rel, idx) => (
                      <div
                        className="flex items-center gap-4 border-b last:border-0 pb-4 mb-4 last:pb-0 last:mb-0"
                        key={idx}
                      >
                        {rel.thumbnailUrl ? (
                          <img
                            alt={rel.title}
                            className="object-cover w-16 h-16 rounded"
                            src={rel.thumbnailUrl}
                          />
                        ) : (
                          <div className="w-16 h-16 rounded flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                            <span
                              className="inline-block"
                              dangerouslySetInnerHTML={{
                                __html: rel.primaryIcon ?? "",
                              }}
                            />
                          </div>
                        )}
                        <div className="flex-1">
                          <Link to={`/hub/resources/${rel.slug}`}>
                            <p className="font-medium hover:underline">
                              {rel.title}
                            </p>
                          </Link>
                          <p className="text-xs text-gray-500 dark:text-gray-400">
                            {rel.resourceType} •{" "}
                            {new Date(
                              rel.createdOrPublishStartDate,
                            ).toLocaleDateString()}
                          </p>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    No recommended resources found.
                  </p>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </MainPageWrapper>
  );
}
