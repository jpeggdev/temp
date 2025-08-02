import axios from "../axiosInstance";
import {
  UpdateStochasticCustomerDoNotMailRequest,
  UpdateStochasticCustomerDoNotMailResponse,
} from "@/api/updateStochasticCustomerDoNotMail/types";

export const updateStochasticCustomerDoNotMail = async (
  customerId: number,
  requestData: UpdateStochasticCustomerDoNotMailRequest,
): Promise<UpdateStochasticCustomerDoNotMailResponse> => {
  const response = await axios.patch<UpdateStochasticCustomerDoNotMailResponse>(
    `/api/private/stochastic-customers/${customerId}/do-not-mail`,
    requestData,
  );
  return response.data;
};
