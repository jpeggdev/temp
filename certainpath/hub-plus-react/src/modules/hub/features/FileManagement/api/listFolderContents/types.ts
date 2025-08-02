export interface Tag {
  id: number;
  name: string;
  color: string | null;
}

export interface ListFolderContentsRequestParams {
  folderUuid?: string | null;
  limit?: number;
  sortBy?: "name" | "fileType" | "updatedAt" | "fileSize";
  sortOrder?: "ASC" | "DESC";
  searchTerm?: string | null;
  cursor?: string | null;
  fileTypes?: string[]; // Added support for filtering by file types
  tags?: number[]; // Added support for filtering by tag IDs
}

export interface FilesystemNode {
  uuid: string;
  name: string;
  fileType: string;
  type: "file" | "folder" | "unknown";
  parentUuid: string | null;
  createdAt: string;
  updatedAt: string;
  tags: Tag[];
  mimeType?: string | null;
  fileSize?: number | null;
  url?: string | null;
  path?: string | null;
}

export interface FolderInfo {
  uuid: string;
  name: string;
  type: "folder";
  parentUuid: string | null;
  createdAt: string;
  updatedAt: string;
  path: string;
}

export interface BreadcrumbItem {
  uuid: string;
  name: string;
}

export interface ListFolderContentsData {
  items: FilesystemNode[];
  currentFolder: FolderInfo | null;
  breadcrumbs: BreadcrumbItem[];
  nextCursor?: string | null;
}

export interface ListFolderContentsResponse {
  data: ListFolderContentsData;
  meta: {
    totalCount: number;
    hasMore?: boolean;
  };
}
