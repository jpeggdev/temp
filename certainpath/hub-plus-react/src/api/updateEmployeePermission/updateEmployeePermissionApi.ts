import axios from "../axiosInstance";
import {
  UpdateEmployeePermissionRequest,
  UpdateEmployeePermissionResponse,
} from "./types";

export const updateEmployeePermission = async (
  uuid: string,
  requestData: UpdateEmployeePermissionRequest,
): Promise<UpdateEmployeePermissionResponse> => {
  const response = await axios.put<UpdateEmployeePermissionResponse>(
    `/api/private/user/${uuid}/update-employee-permission`,
    requestData,
  );
  return response.data;
};
