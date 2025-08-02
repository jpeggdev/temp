import { axiosInstance } from "../axiosInstance";
import {
  GetEventVenueLookupRequest,
  GetEventVenueLookupResponse,
} from "./types";

export async function getEventVenueLookupApi(
  requestData: GetEventVenueLookupRequest,
): Promise<GetEventVenueLookupResponse> {
  const response = await axiosInstance.get<GetEventVenueLookupResponse>(
    "/api/private/event-venues/lookup",
    {
      params: requestData,
    },
  );
  return response.data;
}
