import axios from "../axiosInstance";
import { FetchBatchStatusesResponse } from "@/api/fetchBatchStatuses/types";

export const fetchBatchStatuses =
  async (): Promise<FetchBatchStatusesResponse> => {
    const response = await axios.get<FetchBatchStatusesResponse>(
      "/api/private/batch-statuses",
    );
    return response.data;
  };
