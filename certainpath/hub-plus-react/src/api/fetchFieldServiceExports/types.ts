export interface FetchFieldServiceExportsRequest {
  page?: number;
  pageSize?: number;
  sortOrder?: "ASC" | "DESC";
}

export interface FetchFieldServiceExportsResponse {
  data: {
    exports: FieldServiceExport[];
  };
  meta?: {
    totalCount: number;
  };
}

export interface FieldServiceExport {
  uuid: string;
  intacctId: string;
  fromEmail?: string | null;
  toEmail?: string | null;
  subject?: string | null;
  emailText?: string | null;
  emailHtml?: string | null;
  attachments: FieldServiceExportAttachment[];
  createdAt: string;
  updatedAt: string;
}

export interface FieldServiceExportAttachment {
  uuid: string;
  originalFilename: string;
  bucketName: string;
  objectKey: string;
  contentType: string;
  createdAt: string;
  updatedAt: string;
}
