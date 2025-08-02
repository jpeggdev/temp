import axios from "@/api/axiosInstance";
import { DeleteTagResponse } from "./types";

export const deleteTag = async (id: number): Promise<DeleteTagResponse> => {
  const response = await axios.delete<DeleteTagResponse>(
    `/api/private/file-management/tags/${id}`,
  );
  return response.data;
};
