import { FetchVenueResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchVenue = async (id: number): Promise<FetchVenueResponse> => {
  const response = await axios.get<FetchVenueResponse>(
    `/api/private/event-venue/${id}`,
  );
  return response.data;
};
