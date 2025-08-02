import React from "react";
import {
  extractVimeoId,
  extractYouTubeId,
} from "@/modules/hub/features/ResourceLibrary/utils/contentBlockUtils";

interface VideoEmbedProps {
  contentUrl: string;
}

export function VideoEmbed({ contentUrl }: VideoEmbedProps) {
  const fallback = (
    <div className="flex items-center justify-center h-full">
      <a
        className="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-primary-light transition"
        href={contentUrl}
        rel="noopener noreferrer"
        target="_blank"
      >
        Watch Video
      </a>
    </div>
  );

  if (contentUrl.includes("vimeo.com")) {
    const vimeoId = extractVimeoId(contentUrl.trim());
    if (vimeoId) {
      return (
        <iframe
          allow="autoplay; fullscreen; picture-in-picture"
          allowFullScreen
          className="absolute inset-0 w-full h-full"
          frameBorder="0"
          src={`https://player.vimeo.com/video/${vimeoId}`}
        />
      );
    }
    return fallback;
  }

  if (contentUrl.includes("youtube.com") || contentUrl.includes("youtu.be")) {
    const youTubeId = extractYouTubeId(contentUrl.trim());
    if (youTubeId) {
      return (
        <iframe
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowFullScreen
          className="absolute inset-0 w-full h-full"
          frameBorder="0"
          src={`https://www.youtube.com/embed/${youTubeId}`}
        />
      );
    }
    return fallback;
  }

  return fallback;
}
