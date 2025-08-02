import axios from "../axiosInstance";
import { DeleteResourceResponse } from "./types";

export const deleteResource = async (
  resourceUuid: string,
): Promise<DeleteResourceResponse> => {
  const response = await axios.delete<DeleteResourceResponse>(
    `/api/private/resource/${resourceUuid}`,
  );
  return response.data;
};
