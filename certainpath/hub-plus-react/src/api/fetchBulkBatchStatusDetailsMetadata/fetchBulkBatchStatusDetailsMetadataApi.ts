import axios from "../axiosInstance";
import {
  FetchBulkBatchStatusDetailsMetadataResponse,
  FetchBulkBatchStatusDetailsMetadataRequest,
} from "./types";

export const fetchBulkBatchStatusDetailsMetadata = async (
  requestParams: FetchBulkBatchStatusDetailsMetadataRequest = {},
): Promise<FetchBulkBatchStatusDetailsMetadataResponse> => {
  const response = await axios.get<FetchBulkBatchStatusDetailsMetadataResponse>(
    "/api/private/details-metadata/bulk-batch-status",
    {
      params: requestParams,
    },
  );
  return response.data;
};
