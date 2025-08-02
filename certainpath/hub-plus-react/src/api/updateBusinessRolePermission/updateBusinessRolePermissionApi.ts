import axios from "../axiosInstance";
import {
  UpdateBusinessRolePermissionRequest,
  UpdateBusinessRolePermissionResponse,
} from "./types";

export const updateBusinessRolePermissions = async (
  payload: UpdateBusinessRolePermissionRequest,
): Promise<UpdateBusinessRolePermissionResponse> => {
  const response = await axios.put<UpdateBusinessRolePermissionResponse>(
    `/api/private/business-role/update-permissions`,
    payload,
  );
  return response.data;
};
