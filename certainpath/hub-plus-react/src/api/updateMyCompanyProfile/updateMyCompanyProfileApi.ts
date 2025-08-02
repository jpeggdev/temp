import axios from "../axiosInstance";
import { UpdateMyCompanyProfileRequest } from "./types";

export const updateMyCompanyProfile = async (
  data: UpdateMyCompanyProfileRequest,
): Promise<void> => {
  await axios.put("/api/private/my-company-profile", data);
};
