import { FetchDiscountResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchDiscount = async (
  id: number,
): Promise<FetchDiscountResponse> => {
  const response = await axios.get<FetchDiscountResponse>(
    `/api/private/event-discount/${id}`,
  );
  return response.data;
};
