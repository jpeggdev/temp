import { FetchUserAppSettingsResponse } from "./types";
import axios from "../axiosInstance";

export const fetchUserAppSettings =
  async (): Promise<FetchUserAppSettingsResponse> => {
    const response = await axios.get<FetchUserAppSettingsResponse>(
      "/api/private/user-app-settings",
    );
    return response.data;
  };
