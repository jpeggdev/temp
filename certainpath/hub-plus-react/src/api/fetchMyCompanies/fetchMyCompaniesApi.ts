import { MyCompaniesResponse } from "./types";
import axios from "../axiosInstance";

export const fetchMyCompanies = async (
  page: number = 1,
  search: string = "",
): Promise<MyCompaniesResponse> => {
  const response = await axios.get<MyCompaniesResponse>(
    `/api/private/my-companies?page=${page}&search=${encodeURIComponent(search)}`,
  );
  return response.data;
};
