import axiosInstance from "@/api/axiosInstance";
import { GetEventCheckoutConfirmationPdfResponse } from "./types";

export const getEventCheckoutConfirmationPdf = async (
  eventCheckoutSessionUuid: string,
): Promise<GetEventCheckoutConfirmationPdfResponse> => {
  const response = await axiosInstance.get<Blob>(
    `/api/private/event-checkout-sessions/${eventCheckoutSessionUuid}/confirmation-download`,
    {
      responseType: "blob",
    },
  );

  return { data: response.data };
};
