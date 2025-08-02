import axios from "@/api/axiosInstance";
import { ListTagsResponse } from "./types";

export const listTags = async (): Promise<ListTagsResponse> => {
  const response = await axios.get<ListTagsResponse>(
    "/api/private/file-management/tags",
  );
  return response.data;
};
