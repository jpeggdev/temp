export interface FetchCampaignFilesRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
}

export interface FetchCampaignFilesResponse {
  data: CampaignFile[];
  meta?: {
    totalCount: number;
  };
}

export interface CampaignFile {
  id: number;
  originalFilename: string;
  bucketName: string;
  objectKey: string;
  contentType: string;
  createdAt?: string | null;
  updatedAt?: string | null;
}
