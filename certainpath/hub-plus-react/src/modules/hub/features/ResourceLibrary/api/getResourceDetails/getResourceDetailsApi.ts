import axios from "../../../../../../api/axiosInstance";
import { GetResourceDetailsResponse } from "./types";

export const getResourceDetails = async (
  slug: string,
): Promise<GetResourceDetailsResponse> => {
  const response = await axios.get<GetResourceDetailsResponse>(
    `/api/private/resource-details/${slug}`,
  );
  return response.data;
};
