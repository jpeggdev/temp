import axios from "../axiosInstance";
import { GetMyCompanyProfileResponse } from "./types";

export const getMyCompanyProfile =
  async (): Promise<GetMyCompanyProfileResponse> => {
    const response = await axios.get<GetMyCompanyProfileResponse>(
      "/api/private/my-company-profile",
    );
    return response.data;
  };
