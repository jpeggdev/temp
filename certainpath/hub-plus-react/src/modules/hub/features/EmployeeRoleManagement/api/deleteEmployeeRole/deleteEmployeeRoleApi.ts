import axios from "../../../../../../api/axiosInstance";
import { DeleteEmployeeRoleResponse } from "./types";

export const deleteEmployeeRole = async (
  id: number,
): Promise<DeleteEmployeeRoleResponse> => {
  const response = await axios.delete<DeleteEmployeeRoleResponse>(
    `/api/private/employee/role/${id}/delete`,
  );
  return response.data;
};
