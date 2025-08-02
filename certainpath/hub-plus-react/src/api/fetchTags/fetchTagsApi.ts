import { FetchTagsResponse } from "./types";
import axios from "../axiosInstance";

export const fetchTags = async (
  page: number = 1,
  search: string = "",
): Promise<FetchTagsResponse> => {
  const response = await axios.get<FetchTagsResponse>(
    `/api/private/company/tags?page=${encodeURIComponent(page)}&searchTerm=${encodeURIComponent(search)}`,
  );
  return response.data;
};
