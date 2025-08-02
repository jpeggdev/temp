import axios from "../axiosInstance";
import { EditUserRequest, EditUserResponse } from "./types";

export const editUser = async (
  uuid: string,
  requestData: EditUserRequest,
): Promise<EditUserResponse> => {
  const response = await axios.put<EditUserResponse>(
    `/api/private/employees/${uuid}/edit`,
    requestData,
  );
  return response.data;
};
