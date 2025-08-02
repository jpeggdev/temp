import axios from "../axiosInstance";
import {
  FavoriteResourceResponse,
  FavoriteResourceRequestParams,
} from "./types";

export const toggleResourceFavorite = async (
  params: FavoriteResourceRequestParams,
): Promise<FavoriteResourceResponse> => {
  const response = await axios.post<FavoriteResourceResponse>(
    `/api/private/resources/${params.resourceUuid}/favorite`,
  );
  return response.data;
};
