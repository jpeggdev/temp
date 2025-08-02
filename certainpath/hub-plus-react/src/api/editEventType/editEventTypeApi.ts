import { axiosInstance } from "../axiosInstance";
import { EditEventTypeResponse, EditEventTypeDTO } from "./types";

export const editEventType = async (
  id: number,
  editEventTypeDTO: EditEventTypeDTO,
): Promise<EditEventTypeResponse> => {
  const response = await axiosInstance.put<EditEventTypeResponse>(
    `/api/private/event-types/${id}/edit`,
    editEventTypeDTO,
  );
  return response.data;
};
