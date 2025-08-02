export interface CreateFolderRequest {
  name?: string | null;
  parentFolderUuid?: string | null;
}

export interface CreateFolderData {
  uuid: string;
  name: string;
  path: string;
  parentUuid: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface CreateFolderResponse {
  data: CreateFolderData;
}
