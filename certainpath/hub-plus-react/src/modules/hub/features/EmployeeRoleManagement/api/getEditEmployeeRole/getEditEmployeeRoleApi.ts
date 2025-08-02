import axios from "../../../../../../api/axiosInstance";
import { GetEditEmployeeRoleResponse } from "./types";

export const getEditEmployeeRole = async (
  id: number,
): Promise<GetEditEmployeeRoleResponse> => {
  const response = await axios.get<GetEditEmployeeRoleResponse>(
    `/api/private/employee/role/${id}`,
  );
  return response.data;
};
