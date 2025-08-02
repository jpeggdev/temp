import axios from "../../../../../../api/axiosInstance";
import {
  ValidateResourceSlugRequest,
  ValidateResourceSlugResponse,
} from "./types";

export const validateResourceSlug = async (
  payload: ValidateResourceSlugRequest,
): Promise<ValidateResourceSlugResponse> => {
  const response = await axios.post<ValidateResourceSlugResponse>(
    "/api/private/resource/validate-slug",
    payload,
  );
  return response.data;
};
