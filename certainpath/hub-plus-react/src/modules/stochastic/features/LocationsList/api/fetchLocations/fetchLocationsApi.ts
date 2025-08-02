import { FetchLocationsRequest, FetchLocationsResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchLocations = async (
  requestData: FetchLocationsRequest,
): Promise<FetchLocationsResponse> => {
  const response = await axios.get<FetchLocationsResponse>(
    "/api/private/locations",
    { params: requestData },
  );
  return response.data;
};
