import React from "react";
import ReactMarkdown from "react-markdown";
import remarkGfm from "remark-gfm";
import "./ResourceDetailsContentBlock.css";
import { FileEmbed } from "@/modules/hub/features/ResourceLibrary/components/FileEmbed/FileEmbed";
import { VideoEmbed } from "@/modules/hub/features/ResourceLibrary/components/VideoEmbed/VideoEmbed";
import { LinkEmbed } from "@/modules/hub/features/ResourceLibrary/components/LinkEmbed/LinkEmbed";

export const BLOCK_TYPES = {
  text: "text",
  image: "image",
  file: "file",
  vimeo: "vimeo",
  youtube: "youtube",
  link: "link",
} as const;

export type BlockType = keyof typeof BLOCK_TYPES;

export interface ContentBlockBase {
  id?: string;
  type: BlockType;
  content: string;
  fileId?: number | null;
  order_number?: number;
  title?: string; // Optional title for image, file, vimeo, YouTube, and link blocks
  shortDescription?: string; // Optional short description for image, file, vimeo, YouTube, and link blocks
}

interface ResourceDetailsContentBlockProps {
  block: ContentBlockBase;
}

export default function ResourceDetailsContentBlock({
  block,
}: ResourceDetailsContentBlockProps) {
  switch (block.type) {
    case "text":
      return (
        <div className="my-6">
          <div className="resource-markdown prose dark:prose-invert max-w-none">
            <ReactMarkdown remarkPlugins={[remarkGfm]}>
              {block.content}
            </ReactMarkdown>
          </div>
        </div>
      );

    case "image":
      return (
        <div className="my-6">
          {block.title && (
            <h3 className="text-xl font-semibold mb-2">{block.title}</h3>
          )}
          {block.shortDescription && (
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
              {block.shortDescription}
            </p>
          )}
          <img
            alt={block.title || "Resource image"}
            className="object-cover w-full h-auto"
            src={block.content}
          />
        </div>
      );

    case "file":
      return (
        <div className="my-6">
          {block.shortDescription && (
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
              {block.shortDescription}
            </p>
          )}
          <FileEmbed
            bypassIframeForPdf={true}
            contentUrl={block.content.trim()}
            title={block.title ?? null}
          />
        </div>
      );
    case "vimeo":
    case "youtube":
      return (
        <div className="my-6">
          {block.title && (
            <h3 className="text-xl font-semibold mb-2">{block.title}</h3>
          )}
          {block.shortDescription && (
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
              {block.shortDescription}
            </p>
          )}
          <div className="relative aspect-video rounded-lg overflow-hidden bg-black">
            <VideoEmbed contentUrl={block.content.trim()} />
          </div>
        </div>
      );

    case "link":
      return (
        <div className="my-6">
          {block.shortDescription && (
            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
              {block.shortDescription}
            </p>
          )}
          <LinkEmbed
            contentUrl={block.content.trim()}
            title={block.title ?? block.content.trim()}
          />
        </div>
      );

    default:
      return null;
  }
}
