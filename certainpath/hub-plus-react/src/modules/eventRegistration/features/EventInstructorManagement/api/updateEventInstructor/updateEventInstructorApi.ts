import axiosInstance from "@/api/axiosInstance";
import {
  UpdateEventInstructorRequest,
  UpdateEventInstructorResponse,
} from "./types";

export const updateEventInstructor = async (
  id: number,
  requestBody: UpdateEventInstructorRequest,
): Promise<UpdateEventInstructorResponse> => {
  const response = await axiosInstance.put<UpdateEventInstructorResponse>(
    `/api/private/event-instructors/${id}/update`,
    requestBody,
  );

  return response.data;
};
