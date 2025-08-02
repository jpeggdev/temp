import axios from "@/api/axiosInstance";
import { CreateAndAssignTagRequest, CreateAndAssignTagResponse } from "./types";

export const createAndAssignTag = async (
  data: CreateAndAssignTagRequest,
): Promise<CreateAndAssignTagResponse> => {
  const response = await axios.post<CreateAndAssignTagResponse>(
    "/api/private/file-management/tags/create-and-assign",
    data,
  );
  return response.data;
};
