import axios from "../../../../../../api/axiosInstance";
import { CreateEmployeeRoleRequest, CreateEmployeeRoleResponse } from "./types";

export const createEmployeeRole = async (
  requestData: CreateEmployeeRoleRequest,
): Promise<CreateEmployeeRoleResponse> => {
  const response = await axios.post<CreateEmployeeRoleResponse>(
    "/api/private/employee/role/create",
    requestData,
  );
  return response.data;
};
