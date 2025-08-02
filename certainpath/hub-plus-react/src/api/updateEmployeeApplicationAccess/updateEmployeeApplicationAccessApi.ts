import axios from "../axiosInstance";
import {
  UpdateEmployeeApplicationAccessRequest,
  UpdateEmployeeApplicationAccessResponse,
} from "./types";

export const updateEmployeeApplicationAccess = async (
  uuid: string,
  requestData: UpdateEmployeeApplicationAccessRequest,
): Promise<UpdateEmployeeApplicationAccessResponse> => {
  const response = await axios.put<UpdateEmployeeApplicationAccessResponse>(
    `/api/private/user/${uuid}/update-employee-application-access`,
    requestData,
  );
  return response.data;
};
