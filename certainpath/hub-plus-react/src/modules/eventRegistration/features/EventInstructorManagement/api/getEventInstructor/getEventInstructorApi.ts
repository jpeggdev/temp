import axiosInstance from "@/api/axiosInstance";
import { GetEventInstructorResponse } from "./types";

export const getEventInstructor = async (
  id: number,
): Promise<GetEventInstructorResponse> => {
  const response = await axiosInstance.get<GetEventInstructorResponse>(
    `/api/private/event-instructors/${id}`,
  );

  return response.data;
};
