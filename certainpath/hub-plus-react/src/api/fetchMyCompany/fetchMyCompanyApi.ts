import { FetchMyCompanyResponse } from "./types";
import { axiosInstanceWithoutInterceptors } from "../axiosInstance";

export const fetchMyCompany = async (
  token: string,
): Promise<FetchMyCompanyResponse> => {
  const impersonateUserUuid = localStorage.getItem("impersonateUserUuid");

  const headers: Record<string, string> = {
    Authorization: `Bearer ${token}`,
  };

  if (impersonateUserUuid) {
    headers["X-Impersonate-User-UUID"] = impersonateUserUuid;
    console.log(`Impersonating user: ${impersonateUserUuid}`);
  }

  const response =
    await axiosInstanceWithoutInterceptors.get<FetchMyCompanyResponse>(
      "/api/private/my-company",
      {
        headers,
      },
    );
  return response.data;
};
