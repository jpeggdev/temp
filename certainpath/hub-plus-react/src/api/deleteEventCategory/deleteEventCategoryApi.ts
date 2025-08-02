import axios from "../axiosInstance";
import { DeleteEventCategoryResponse } from "./types";

export const deleteEventCategory = async (
  id: number,
): Promise<DeleteEventCategoryResponse> => {
  const response = await axios.delete<DeleteEventCategoryResponse>(
    `/api/private/event-categories/${id}`,
  );
  return response.data;
};
