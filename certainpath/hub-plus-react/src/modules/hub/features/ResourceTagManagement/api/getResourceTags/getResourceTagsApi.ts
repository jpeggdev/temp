import axios from "../../../../../../api/axiosInstance";
import { GetResourceTagsRequest, GetResourceTagsResponse } from "./types";

export const getResourceTags = async (
  queryParams: GetResourceTagsRequest,
): Promise<GetResourceTagsResponse> => {
  const response = await axios.get<GetResourceTagsResponse>(
    "/api/private/resource-tags",
    {
      params: queryParams,
    },
  );
  return response.data;
};
