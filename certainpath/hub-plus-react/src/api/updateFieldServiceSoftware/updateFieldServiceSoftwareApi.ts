import axios from "../axiosInstance";
import {
  UpdateFieldServiceSoftwareDTO,
  UpdateFieldServiceSoftwareResponse,
} from "./types";

export const updateFieldServiceSoftware = async (
  uuid: string,
  updateFieldServiceSoftwareDTO: UpdateFieldServiceSoftwareDTO,
): Promise<UpdateFieldServiceSoftwareResponse> => {
  const response = await axios.put<UpdateFieldServiceSoftwareResponse>(
    `/api/private/companies/${uuid}/field-service-software`,
    updateFieldServiceSoftwareDTO,
  );
  return response.data;
};
