import { FetchCompaniesRequest, FetchCompaniesResponse } from "./types";
import { axiosInstance } from "../axiosInstance";

export const fetchCompanies = async (
  requestData: FetchCompaniesRequest,
): Promise<FetchCompaniesResponse> => {
  const params = {
    ...requestData,
  };

  const response = await axiosInstance.get<FetchCompaniesResponse>(
    "/api/private/companies",
    {
      params,
    },
  );
  return response.data;
};
