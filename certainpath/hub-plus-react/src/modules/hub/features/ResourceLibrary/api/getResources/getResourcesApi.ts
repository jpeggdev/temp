import axios from "../../../../../../api/axiosInstance";
import { GetResourcesRequest, GetResourcesResponse } from "./types";

export const getResources = async (
  params: GetResourcesRequest,
): Promise<GetResourcesResponse> => {
  const response = await axios.get<GetResourcesResponse>(
    "/api/private/resources/search",
    { params },
  );
  return response.data;
};
