// src/modules/hub/features/FileManagement/data/filterData.ts
import React from "react";
import {
  Folder,
  FileText,
  Image,
  Video,
  Music,
  Archive,
  Code,
  FileIcon,
} from "lucide-react";

export interface FileType {
  id: string;
  name: string;
  icon: React.ReactNode;
}

export interface Tag {
  id: number;
  name: string;
  color: string;
}

// File types for filtering
export const fileTypes: FileType[] = [
  {
    id: "folder",
    name: "Folders",
    icon: <Folder className="text-yellow-500" size={16} />,
  },
  {
    id: "document",
    name: "Documents",
    icon: <FileText className="text-blue-500" size={16} />,
  },
  {
    id: "image",
    name: "Images",
    icon: <Image className="text-green-500" size={16} />,
  },
  {
    id: "video",
    name: "Videos",
    icon: <Video className="text-purple-500" size={16} />,
  },
  {
    id: "audio",
    name: "Audio",
    icon: <Music className="text-pink-500" size={16} />,
  },
  {
    id: "archive",
    name: "Archives",
    icon: <Archive className="text-orange-500" size={16} />,
  },
  {
    id: "code",
    name: "Code",
    icon: <Code className="text-cyan-500" size={16} />,
  },
  {
    id: "other",
    name: "Other",
    icon: <FileIcon className="text-gray-500" size={16} />,
  },
];

// Tags for filtering
export const tags: Tag[] = [
  { id: 1, name: "Important", color: "#e74c3c" },
  { id: 2, name: "Work", color: "#3498db" },
  { id: 3, name: "Personal", color: "#2ecc71" },
  { id: 4, name: "Project A", color: "#9b59b6" },
  { id: 5, name: "Project B", color: "#f39c12" },
  { id: 6, name: "Archived", color: "#7f8c8d" },
  { id: 7, name: "Reference", color: "#1abc9c" },
  { id: 8, name: "Shared", color: "#e67e22" },
];
