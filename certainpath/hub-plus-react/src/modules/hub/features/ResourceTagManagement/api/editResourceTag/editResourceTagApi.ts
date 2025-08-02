import axios from "../../../../../../api/axiosInstance";
import { EditResourceTagRequest, EditResourceTagResponse } from "./types";

export const editResourceTag = async (
  tagId: number,
  requestData: EditResourceTagRequest,
): Promise<EditResourceTagResponse> => {
  const response = await axios.put<EditResourceTagResponse>(
    `/api/private/resource/tag/${tagId}/edit`,
    requestData,
  );
  return response.data;
};
