import axios from "../axiosInstance";
import {
  UpdateResourceViewCountRequestParams,
  UpdateResourceViewCountResponse,
} from "./types";

export const updateResourceViewCount = async (
  params: UpdateResourceViewCountRequestParams,
): Promise<UpdateResourceViewCountResponse> => {
  const response = await axios.post<UpdateResourceViewCountResponse>(
    `/api/private/resources/${params.resourceUuid}/views`,
  );
  return response.data;
};
