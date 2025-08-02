import axios from "../../../../../../api/axiosInstance";
import {
  FetchCityFilterOptionsRequest,
  FetchCityFilterOptionsResponse,
} from "./types";

export const fetchCityFilterOptions = async (
  requestData?: FetchCityFilterOptionsRequest,
): Promise<FetchCityFilterOptionsResponse> => {
  const response = await axios.get<FetchCityFilterOptionsResponse>(
    `/api/private/filter-option/cities`,
    { params: requestData },
  );

  return response.data;
};
