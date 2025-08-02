import axios from "../../../../../../api/axiosInstance";
import {
  FetchYearFilterOptionsRequest,
  FetchYearFilterOptionsResponse,
} from "./types";

export const fetchYearFilterOptions = async (
  requestData?: FetchYearFilterOptionsRequest,
): Promise<FetchYearFilterOptionsResponse> => {
  const response = await axios.get<FetchYearFilterOptionsResponse>(
    `/api/private/filter-option/years`,
    { params: requestData },
  );

  return response.data;
};
