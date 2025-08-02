import { axiosInstance } from "../axiosInstance";
import { EditEventCategoryResponse, EditEventCategoryDTO } from "./types";

export const editEventCategory = async (
  id: number,
  editEventCategoryDTO: EditEventCategoryDTO,
): Promise<EditEventCategoryResponse> => {
  const response = await axiosInstance.put<EditEventCategoryResponse>(
    `/api/private/event-categories/${id}/edit`,
    editEventCategoryDTO,
  );
  return response.data;
};
