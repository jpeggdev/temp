export interface ReplaceFileData {
  uuid: string;
  name: string;
  originalName: string;
  url: string;
  type: string;
  mimeType: string;
  fileSize: number;
  fileType: string;
  replacedAt: string;
}

export interface ReplaceFileResponse {
  data: ReplaceFileData;
}
