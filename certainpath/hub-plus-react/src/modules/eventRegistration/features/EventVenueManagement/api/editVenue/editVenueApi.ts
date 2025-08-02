import axios from "../../../../../../api/axiosInstance";
import { EditVenueRequest, EditVenueResponse } from "./types";

export const updateVenue = async (
  id: number,
  requestData: EditVenueRequest,
): Promise<EditVenueResponse> => {
  const response = await axios.put<EditVenueResponse>(
    `/api/private/event-venue/${id}/edit`,
    requestData,
  );
  return response.data;
};
