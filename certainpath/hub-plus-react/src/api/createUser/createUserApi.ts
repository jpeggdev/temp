import axios from "../axiosInstance";
import { CreateUserRequest, CreateUserResponse } from "./types";

export const createUser = async (
  requestData: CreateUserRequest,
): Promise<CreateUserResponse> => {
  const response = await axios.post<CreateUserResponse>(
    `/api/private/employees/create`,
    requestData,
  );
  return response.data;
};
