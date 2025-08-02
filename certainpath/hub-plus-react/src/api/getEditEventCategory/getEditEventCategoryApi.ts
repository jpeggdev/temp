import axios from "../axiosInstance";
import { GetEditEventCategoryResponse } from "./types";

export const getEditEventCategory = async (
  id: number,
): Promise<GetEditEventCategoryResponse> => {
  const response = await axios.get<GetEditEventCategoryResponse>(
    `/api/private/event-category/${id}`,
  );
  return response.data;
};
