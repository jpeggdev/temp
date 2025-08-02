import { FetchTimezonesRequest, FetchTimezonesResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchTimezones = async (
  requestData: FetchTimezonesRequest,
): Promise<FetchTimezonesResponse> => {
  const response = await axios.get<FetchTimezonesResponse>(
    "/api/private/timezones",
    { params: requestData },
  );
  return response.data;
};
