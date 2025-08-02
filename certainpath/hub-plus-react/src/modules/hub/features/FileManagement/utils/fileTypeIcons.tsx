// src/modules/hub/features/FileManagement/utils/fileTypeIcons.tsx
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
import { FileTypeStatDTO } from "../api/getFileManagerMetaData/types";

export interface FileTypeWithIcon {
  id: string;
  name: string;
  icon: React.ReactNode;
  count: number;
}

export const getFileTypeIcon = (type: string): React.ReactNode => {
  switch (type.toLowerCase()) {
    case "folder":
      return <Folder className="text-yellow-500" size={16} />;
    case "document":
      return <FileText className="text-blue-500" size={16} />;
    case "image":
      return <Image className="text-green-500" size={16} />;
    case "video":
      return <Video className="text-purple-500" size={16} />;
    case "audio":
      return <Music className="text-pink-500" size={16} />;
    case "archive":
      return <Archive className="text-orange-500" size={16} />;
    case "code":
      return <Code className="text-cyan-500" size={16} />;
    default:
      return <FileIcon className="text-gray-500" size={16} />;
  }
};

export const mapFileTypesWithIcons = (
  fileTypes: FileTypeStatDTO[],
): FileTypeWithIcon[] => {
  return fileTypes.map((fileType) => ({
    id: fileType.type,
    name: fileType.type.charAt(0).toUpperCase() + fileType.type.slice(1),
    icon: getFileTypeIcon(fileType.type),
    count: fileType.count,
  }));
};
