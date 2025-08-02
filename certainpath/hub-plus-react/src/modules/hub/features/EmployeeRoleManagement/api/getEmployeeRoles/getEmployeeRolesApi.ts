import axios from "../../../../../../api/axiosInstance";
import { GetEmployeeRolesRequest, GetEmployeeRolesResponse } from "./types";

export const getEmployeeRoles = async (
  params: GetEmployeeRolesRequest,
): Promise<GetEmployeeRolesResponse> => {
  const response = await axios.get<GetEmployeeRolesResponse>(
    "/api/private/employee-roles",
    {
      params,
    },
  );
  return response.data;
};
