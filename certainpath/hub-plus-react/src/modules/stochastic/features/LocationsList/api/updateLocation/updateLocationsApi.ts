import { UpdateLocationRequest, UpdateLocationResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const updateLocation = async (
  id: number,
  requestData: UpdateLocationRequest,
): Promise<UpdateLocationResponse> => {
  const response = await axios.put<UpdateLocationResponse>(
    `/api/private/location/${id}`,
    requestData,
  );
  return response.data;
};
