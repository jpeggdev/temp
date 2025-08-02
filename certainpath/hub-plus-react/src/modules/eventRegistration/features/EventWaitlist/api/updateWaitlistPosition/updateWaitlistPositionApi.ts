import axiosInstance from "@/api/axiosInstance";
import {
  UpdateWaitlistPositionRequest,
  UpdateWaitlistPositionResponse,
} from "./types";

export const updateWaitlistPosition = async (
  requestData: UpdateWaitlistPositionRequest,
): Promise<UpdateWaitlistPositionResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/waitlist/update-position`;

  const response = await axiosInstance.post<UpdateWaitlistPositionResponse>(
    url,
    {
      eventWaitlistId: requestData.eventWaitlistId,
      newPosition: requestData.newPosition,
    },
  );

  return response.data;
};
