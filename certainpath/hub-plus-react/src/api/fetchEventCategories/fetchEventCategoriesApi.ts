import {
  FetchEventCategoriesRequest,
  FetchEventCategoriesResponse,
} from "./types";
import { axiosInstance } from "../axiosInstance";

export const fetchEventCategories = async (
  requestData: FetchEventCategoriesRequest,
): Promise<FetchEventCategoriesResponse> => {
  const params = {
    ...requestData,
  };

  const response = await axiosInstance.get<FetchEventCategoriesResponse>(
    "/api/private/event-categories",
    {
      params,
    },
  );
  return response.data;
};
