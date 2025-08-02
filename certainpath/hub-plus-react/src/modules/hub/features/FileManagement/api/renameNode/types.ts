export interface RenameNodeRequest {
  name: string;
}

export interface RenameNodeData {
  uuid: string;
  name: string;
  type: "file" | "folder" | "unknown";
  parentUuid: string | null;
  createdAt: string;
  updatedAt: string;
  mimeType?: string | null;
  fileSize?: number | null;
  url?: string | null;
  path?: string | null;
}

export interface RenameNodeResponse {
  data: RenameNodeData;
}
