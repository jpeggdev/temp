export interface UploadedFileInfo {
  uuid: string;
  name: string;
  fileType: string;
  type: string;
  parentUuid: string | null;
  createdAt: string;
  updatedAt: string;
  tags: Tag[];
  mimeType: string | null;
  fileSize: number | null;
  url: string | null;
}

export interface UploadFilesystemNodesResponse {
  data: {
    files: UploadedFileInfo[];
  };
}

export interface Tag {
  id: number;
  name: string;
  color: string;
}
