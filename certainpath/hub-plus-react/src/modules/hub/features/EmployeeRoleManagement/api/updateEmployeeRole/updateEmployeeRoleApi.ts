import axios from "../../../../../../api/axiosInstance";
import { UpdateEmployeeRoleRequest, UpdateEmployeeRoleResponse } from "./types";

export const updateEmployeeRole = async (
  id: number,
  requestData: UpdateEmployeeRoleRequest,
): Promise<UpdateEmployeeRoleResponse> => {
  const response = await axios.put<UpdateEmployeeRoleResponse>(
    `/api/private/employee/role/${id}`,
    requestData,
  );
  return response.data;
};
