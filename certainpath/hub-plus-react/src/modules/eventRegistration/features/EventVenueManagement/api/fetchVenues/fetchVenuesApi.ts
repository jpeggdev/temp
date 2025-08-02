import { FetchVenuesRequest, FetchVenuesResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchVenues = async (
  requestData: FetchVenuesRequest,
): Promise<FetchVenuesResponse> => {
  const response = await axios.get<FetchVenuesResponse>(
    "/api/private/event-venues",
    { params: requestData },
  );
  return response.data;
};
