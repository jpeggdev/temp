import axios from "../axiosInstance";
import {
  UpdateCustomerDoNotMailRequest,
  UpdateCustomerDoNotMailResponse,
} from "./types";

export const updateCustomerDoNotMail = async (
  customerId: number,
  requestData: UpdateCustomerDoNotMailRequest,
): Promise<UpdateCustomerDoNotMailResponse> => {
  const response = await axios.patch<UpdateCustomerDoNotMailResponse>(
    `/api/customers/${customerId}/do-not-mail`,
    requestData,
  );
  return response.data;
};
