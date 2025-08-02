import axiosInstance from "@/api/axiosInstance";
import { DeleteEventInstructorResponse } from "./types";

export const deleteEventInstructor = async (
  id: number,
): Promise<DeleteEventInstructorResponse> => {
  const response = await axiosInstance.delete<DeleteEventInstructorResponse>(
    `/api/private/event-instructors/${id}/delete`,
  );

  return response.data;
};
