import axios from "../axiosInstance";
import { GetEditUserDetailsResponse } from "./types";

export const getEditUserDetails = async (
  uuid: string,
): Promise<GetEditUserDetailsResponse> => {
  const response = await axios.get<GetEditUserDetailsResponse>(
    `/api/private/edit-user-details/${uuid}`,
  );
  return response.data;
};
