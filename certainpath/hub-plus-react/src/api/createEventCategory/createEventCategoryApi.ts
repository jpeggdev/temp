import axios from "../axiosInstance";
import {
  CreateEventCategoryRequest,
  CreateEventCategoryResponse,
} from "./types";

export const createEventCategory = async (
  requestData: CreateEventCategoryRequest,
): Promise<CreateEventCategoryResponse> => {
  const response = await axios.post<CreateEventCategoryResponse>(
    `/api/private/event-categories/create`,
    requestData,
  );
  return response.data;
};
