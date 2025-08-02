export interface UploadFieldServiceSoftwareDTO {
  file: File;
  software: string;
  trade: string;
  importType: string;
}

export interface UploadFieldServiceSoftwareResponse {
  data: {
    message: string;
    importId: number;
  };
}
