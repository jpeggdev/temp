export interface TagStatDTO {
  id: number;
  name: string;
  color: string | null;
  count: number;
}

export interface FileTypeStatDTO {
  type: string;
  count: number;
}

export interface GetFileManagerMetaDataResponse {
  data: {
    tags: TagStatDTO[];
    fileTypes: FileTypeStatDTO[];
  };
}
