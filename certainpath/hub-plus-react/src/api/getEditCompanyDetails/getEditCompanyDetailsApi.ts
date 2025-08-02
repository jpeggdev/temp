import axios from "../axiosInstance";
import { GetEditCompanyDetailsResponse } from "./types";

export const getEditCompanyDetails = async (
  uuid: string,
): Promise<GetEditCompanyDetailsResponse> => {
  const response = await axios.get<GetEditCompanyDetailsResponse>(
    `/api/private/edit-company-details/${uuid}`,
  );
  return response.data;
};
