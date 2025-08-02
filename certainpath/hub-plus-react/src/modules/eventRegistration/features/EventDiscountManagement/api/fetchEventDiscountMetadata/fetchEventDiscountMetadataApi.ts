import { FetchDiscountMetadataResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchDiscountMetadata =
  async (): Promise<FetchDiscountMetadataResponse> => {
    const response = await axios.get<FetchDiscountMetadataResponse>(
      `/api/private/event-discount-metadata`,
    );
    return response.data;
  };
