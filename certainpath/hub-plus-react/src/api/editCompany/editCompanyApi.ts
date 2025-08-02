import axios from "../axiosInstance";
import { EditCompanyResponse, EditCompanyDTO } from "./types";

export const editCompany = async (
  uuid: string,
  editCompanyDTO: EditCompanyDTO,
): Promise<EditCompanyResponse> => {
  const response = await axios.put<EditCompanyResponse>(
    `/api/private/companies/${uuid}/edit`,
    editCompanyDTO,
  );
  return response.data;
};
