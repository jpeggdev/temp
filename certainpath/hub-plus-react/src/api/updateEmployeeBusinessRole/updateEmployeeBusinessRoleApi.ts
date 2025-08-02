import axios from "../axiosInstance";
import {
  UpdateEmployeeBusinessRoleRequest,
  UpdateEmployeeBusinessRoleResponse,
} from "./types";

export const updateEmployeeBusinessRole = async (
  uuid: string,
  requestData: UpdateEmployeeBusinessRoleRequest,
): Promise<UpdateEmployeeBusinessRoleResponse> => {
  const response = await axios.put<UpdateEmployeeBusinessRoleResponse>(
    `/api/private/user/${uuid}/update-employee-business-role`,
    requestData,
  );
  return response.data;
};
