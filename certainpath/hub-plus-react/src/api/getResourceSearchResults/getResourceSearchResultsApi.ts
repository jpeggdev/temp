import axios from "../axiosInstance";
import {
  GetResourceSearchResultsAPIResponse,
  GetResourceSearchResultsRequest,
} from "./types";

export const getResourceSearchResults = async (
  params: GetResourceSearchResultsRequest,
): Promise<GetResourceSearchResultsAPIResponse> => {
  const response = await axios.get<GetResourceSearchResultsAPIResponse>(
    "/api/private/resources/search",
    { params },
  );
  return response.data;
};
