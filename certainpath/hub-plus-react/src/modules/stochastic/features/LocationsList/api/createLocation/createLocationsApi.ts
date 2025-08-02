import { CreateLocationRequest, CreateLocationResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const createLocation = async (
  requestData: CreateLocationRequest,
): Promise<CreateLocationResponse> => {
  const response = await axios.post<CreateLocationResponse>(
    "/api/private/location/create",
    requestData,
  );
  return response.data;
};
