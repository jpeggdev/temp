import axios from "@/api/axiosInstance";
import {
  ListFolderContentsRequestParams,
  ListFolderContentsResponse,
} from "./types";

export const listFolderContents = async (
  params: ListFolderContentsRequestParams,
): Promise<ListFolderContentsResponse> => {
  const response = await axios.get<ListFolderContentsResponse>(
    "/api/private/file-management/folders/contents",
    {
      params,
    },
  );
  return response.data;
};
