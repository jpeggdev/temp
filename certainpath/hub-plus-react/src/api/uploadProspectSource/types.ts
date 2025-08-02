export interface UploadProspectSourceDTO {
  file: File;
  software: string;
  importType: string;
  tag: string;
}

export interface UploadProspectSourceResponse {
  data: {
    jobId: string;
  };
}
