import axios from "../axiosInstance";
import {
  SetFeaturedResourceRequest,
  SetFeaturedResourceAPIResponse,
} from "./types";

export const setFeaturedResource = async (
  uuid: string,
  requestData: SetFeaturedResourceRequest,
): Promise<SetFeaturedResourceAPIResponse> => {
  const response = await axios.patch<SetFeaturedResourceAPIResponse>(
    `/api/private/resources/${uuid}/featured`,
    requestData,
  );
  return response.data;
};
