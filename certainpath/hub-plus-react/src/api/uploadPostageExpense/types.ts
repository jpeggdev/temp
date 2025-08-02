export interface UploadPostageExpenseDTO {
  vendor: string;
  type: string;
  file: File;
}

export interface UploadPostageExpenseResponse {
  data: {
    message: string;
  };
}
