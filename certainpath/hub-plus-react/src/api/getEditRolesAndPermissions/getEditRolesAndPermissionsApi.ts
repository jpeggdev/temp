import axios from "../axiosInstance";
import { GetEditRolesAndPermissionsResponse } from "./types";

export const getEditRolesAndPermissions =
  async (): Promise<GetEditRolesAndPermissionsResponse> => {
    const response = await axios.get<GetEditRolesAndPermissionsResponse>(
      `/api/private/edit-roles-and-permissions`,
    );
    return response.data;
  };
