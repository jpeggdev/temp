import axios from "../axiosInstance";
import { GetMyUserProfileResponse } from "./types";

export const getMyUserProfile = async (): Promise<GetMyUserProfileResponse> => {
  const response = await axios.get<GetMyUserProfileResponse>(
    "/api/private/my-user-profile",
  );
  return response.data;
};
