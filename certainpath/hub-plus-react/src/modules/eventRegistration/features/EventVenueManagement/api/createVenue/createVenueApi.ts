import axios from "../../../../../../api/axiosInstance";
import { CreateVenueRequest, CreateVenueResponse } from "./types";

export const createVenue = async (
  requestData: CreateVenueRequest,
): Promise<CreateVenueResponse> => {
  const response = await axios.post<CreateVenueResponse>(
    "/api/private/event-venue/create",
    requestData,
  );
  return response.data;
};
