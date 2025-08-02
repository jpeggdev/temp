import axios from "../../../../../../api/axiosInstance";
import { GetEditResourceTagResponse } from "./types";

export const getEditResourceTag = async (
  id: number,
): Promise<GetEditResourceTagResponse> => {
  const response = await axios.get<GetEditResourceTagResponse>(
    `/api/private/resource/tag/${id}`,
  );
  return response.data;
};
