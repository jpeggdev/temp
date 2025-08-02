import axios from "../axiosInstance";
import { CreateCompanyRequest, CreateCompanyResponse } from "./types";

export const createCompany = async (
  requestData: CreateCompanyRequest,
): Promise<CreateCompanyResponse> => {
  const response = await axios.post<CreateCompanyResponse>(
    `/api/private/companies/create`,
    requestData,
  );
  return response.data;
};
