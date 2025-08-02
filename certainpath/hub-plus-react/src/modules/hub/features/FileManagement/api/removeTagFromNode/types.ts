export interface RemoveTagFromNodeRequest {
  tagId: number;
  filesystemNodeUuid: string;
}

export interface RemoveTagFromNodeResponse {
  data: {
    message: string;
    tagId: number;
    filesystemNodeUuid: string;
  };
}
