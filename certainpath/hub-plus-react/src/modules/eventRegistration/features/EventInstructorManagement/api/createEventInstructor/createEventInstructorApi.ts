import axiosInstance from "@/api/axiosInstance";
import {
  CreateEventInstructorRequest,
  CreateEventInstructorResponse,
} from "./types";

export const createEventInstructor = async (
  requestBody: CreateEventInstructorRequest,
): Promise<CreateEventInstructorResponse> => {
  const response = await axiosInstance.post<CreateEventInstructorResponse>(
    "/api/private/event-instructors/create",
    requestBody,
  );

  return response.data;
};
