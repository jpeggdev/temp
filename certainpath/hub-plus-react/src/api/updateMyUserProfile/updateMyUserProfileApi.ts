import axios from "../axiosInstance";
import { UpdateMyUserProfileRequest } from "./types";

export const updateMyUserProfile = async (
  data: UpdateMyUserProfileRequest,
): Promise<void> => {
  await axios.put("/api/private/my-user-profile", data);
};
