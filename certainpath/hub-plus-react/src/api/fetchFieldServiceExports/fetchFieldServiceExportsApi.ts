import {
  FetchFieldServiceExportsRequest,
  FetchFieldServiceExportsResponse,
} from "./types";
import axios from "../axiosInstance";

export const fetchFieldServiceExports = async (
  requestData: FetchFieldServiceExportsRequest,
): Promise<FetchFieldServiceExportsResponse> => {
  const response = await axios.get<FetchFieldServiceExportsResponse>(
    "/api/private/field-service-exports",
    { params: requestData },
  );
  return response.data;
};
