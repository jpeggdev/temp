export interface FilesystemNodeDetails {
  uuid: string;
  name: string;
  fileType: string;
  type: string;
  parentUuid: string | null;
  createdAt: string;
  updatedAt: string;
  tags: Tag[];
  mimeType?: string | null;
  fileSize?: number | null;
  url?: string | null;
  path?: string | null;

  // New fields
  createdBy?: UserInfo | null;
  updatedBy?: UserInfo | null;
  md5Hash?: string | null;
  duplicates?: DuplicateInfo | null;
  usages?: UsageInfo | null;
  presignedUrl?: string | null;
}

export interface GetFileSystemNodeDetailsResponse {
  data: FilesystemNodeDetails;
}

export interface Tag {
  id: number;
  name: string;
  color: string | null;
}

export interface UserInfo {
  id: number;
  uuid: string;
  firstName: string;
  lastName: string;
  email?: string | null;
}

export interface DuplicateInfo {
  count: number;
  files: DuplicateFile[];
}

export interface DuplicateFile {
  uuid: string;
  name: string;
  path: string;
  fileSize?: number | null;
  createdAt: string;
}

export interface UsageInfo {
  count: number;
  events: EventUsage[];
  resources: ResourceUsage[];
}

export interface EventUsage {
  uuid: string;
  name: string;
  usageType: "thumbnail" | "attachment";
}

export interface ResourceUsage {
  uuid: string;
  name: string;
  usageType: "thumbnail" | "content";
  blockType?: string;
}
