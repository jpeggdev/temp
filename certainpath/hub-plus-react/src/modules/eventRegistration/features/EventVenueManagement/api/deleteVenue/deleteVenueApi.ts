import { DeleteVenueResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const deleteVenue = async (id: number): Promise<DeleteVenueResponse> => {
  const response = await axios.delete<DeleteVenueResponse>(
    `/api/private/event-venue/${id}`,
  );
  return response.data;
};
