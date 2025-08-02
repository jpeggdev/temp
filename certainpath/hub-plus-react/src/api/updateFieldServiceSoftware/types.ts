export interface UpdateFieldServiceSoftwareDTO {
  fieldServiceSoftwareId: number;
}

export interface UpdateFieldServiceSoftwareResponse {
  data: {
    message: string;
    fieldServiceSoftwareId: number | null;
    fieldServiceSoftwareName: string | null;
  };
}
