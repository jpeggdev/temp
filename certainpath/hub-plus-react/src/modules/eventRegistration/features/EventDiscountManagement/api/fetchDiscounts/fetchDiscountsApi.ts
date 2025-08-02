import { FetchDiscountsRequest, FetchDiscountsResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchDiscounts = async (
  requestData: FetchDiscountsRequest,
): Promise<FetchDiscountsResponse> => {
  const response = await axios.get<FetchDiscountsResponse>(
    "/api/private/event-discounts",
    { params: requestData },
  );
  return response.data;
};
