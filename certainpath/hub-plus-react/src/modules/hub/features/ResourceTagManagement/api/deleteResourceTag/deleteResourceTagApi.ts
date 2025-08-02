import axios from "../../../../../../api/axiosInstance";
import { DeleteResourceTagResponse } from "./types";

export const deleteResourceTag = async (
  tagId: number,
): Promise<DeleteResourceTagResponse> => {
  const response = await axios.delete<DeleteResourceTagResponse>(
    `/api/private/resource/tag/${tagId}/delete`,
  );
  return response.data;
};
