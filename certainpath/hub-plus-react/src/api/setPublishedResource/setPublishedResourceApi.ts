import axios from "../axiosInstance";
import {
  SetPublishedResourceRequest,
  SetPublishedResourceAPIResponse,
} from "./types";

export const setPublishedResource = async (
  uuid: string,
  requestData: SetPublishedResourceRequest,
): Promise<SetPublishedResourceAPIResponse> => {
  const response = await axios.patch<SetPublishedResourceAPIResponse>(
    `/api/private/resources/${uuid}/published`,
    requestData,
  );
  return response.data;
};
