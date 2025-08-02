import { GetLocationResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const getLocation = async (id: number): Promise<GetLocationResponse> => {
  const response = await axios.get<GetLocationResponse>(
    `/api/private/location/${id}`,
  );
  return response.data;
};
