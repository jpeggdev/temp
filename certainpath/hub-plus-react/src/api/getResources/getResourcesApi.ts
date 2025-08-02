import axios from "../axiosInstance";
import { GetResourcesRequest, GetResourcesAPIResponse } from "./types";

export const getResources = async (
  params: GetResourcesRequest,
): Promise<GetResourcesAPIResponse> => {
  const response = await axios.get<GetResourcesAPIResponse>(
    "/api/private/resources",
    {
      params,
    },
  );
  return response.data;
};
