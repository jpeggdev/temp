import React from "react";
import {
  DocumentTextIcon,
  PlayCircleIcon,
  MicrophoneIcon,
  EllipsisVerticalIcon,
} from "@heroicons/react/24/outline";
import { cn } from "@/lib/utils";

interface ContentTypeBadgeProps {
  type: string;
  className?: string;
  size?: "sm" | "md" | "lg";
}

const BADGE_STYLES = {
  Document:
    "bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800",
  Video:
    "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800",
  Podcast:
    "bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800",
} as const;

const ICONS = {
  Document: DocumentTextIcon,
  Video: PlayCircleIcon,
  Podcast: MicrophoneIcon,
} as const;

const LABELS = {
  Document: "Document",
  Video: "Video",
  Podcast: "Podcast",
} as const;

export function ContentTypeBadge({
  type,
  className,
  size = "md",
}: ContentTypeBadgeProps) {
  // Fallback icon & label if type is unrecognized
  const Icon = ICONS[type as keyof typeof ICONS] || EllipsisVerticalIcon;
  const badgeStyle =
    BADGE_STYLES[type as keyof typeof BADGE_STYLES] ||
    "bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700";
  const label = LABELS[type as keyof typeof LABELS] || type;

  const sizeClasses = {
    sm: "px-2 py-0.5 text-xs",
    md: "px-2.5 py-0.5 text-xs",
    lg: "px-3 py-1 text-sm",
  };

  const iconSizes = {
    sm: "w-3 h-3",
    md: "w-3.5 h-3.5",
    lg: "w-4 h-4",
  };

  return (
    <div
      className={cn(
        "inline-flex items-center rounded-full font-medium border",
        sizeClasses[size],
        badgeStyle,
        className,
      )}
    >
      <Icon className={cn(iconSizes[size], "mr-1")} />
      {label}
    </div>
  );
}
