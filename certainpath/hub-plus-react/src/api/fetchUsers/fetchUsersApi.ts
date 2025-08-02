import { FetchUsersRequest, FetchUsersResponse } from "./types";
import axios from "../axiosInstance";

export const fetchUsers = async (
  requestData: FetchUsersRequest,
): Promise<FetchUsersResponse> => {
  const response = await axios.get<FetchUsersResponse>(
    "/api/private/employees",
    {
      params: requestData,
    },
  );
  return response.data;
};
