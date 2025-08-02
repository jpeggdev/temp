import axios from "../axiosInstance";
import { GetResourceResponse } from "./types";

export const getResource = async (
  uuid: string,
): Promise<GetResourceResponse> => {
  const response = await axios.get<GetResourceResponse>(
    `/api/private/resource/${uuid}`,
  );
  return response.data;
};
