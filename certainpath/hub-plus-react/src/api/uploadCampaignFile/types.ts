export interface UploadCampaignFilesRequest {
  campaignId: number;
  files: File[];
}

export type UploadCampaignFilesResponse = void;
