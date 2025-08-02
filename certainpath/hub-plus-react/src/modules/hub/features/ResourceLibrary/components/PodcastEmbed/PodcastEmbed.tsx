import React from "react";

interface PodcastEmbedProps {
  contentUrl: string;
}

export function PodcastEmbed({ contentUrl }: PodcastEmbedProps) {
  if (contentUrl.toLowerCase().endsWith(".mp3")) {
    return (
      <div className="bg-gray-100 dark:bg-gray-800 rounded-lg p-4">
        <audio className="w-full" controls preload="metadata">
          <source src={contentUrl} type="audio/mpeg" />
          Your browser does not support the audio element.
        </audio>
      </div>
    );
  }

  if (contentUrl.includes("spotify.com")) {
    const episodeId = contentUrl.split("/").pop() || "";
    return (
      <iframe
        allow="encrypted-media"
        className="rounded-lg"
        frameBorder="0"
        height="232"
        src={`https://open.spotify.com/embed/episode/${episodeId}`}
        width="100%"
      />
    );
  }

  return (
    <a
      className="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-primary-light transition"
      href={contentUrl}
      rel="noopener noreferrer"
      target="_blank"
    >
      Listen to Podcast
    </a>
  );
}
