import axios from "../../../../../../api/axiosInstance";
import { DeleteLocationResponse } from "./types";

export const deleteLocation = async (
  id: number,
): Promise<DeleteLocationResponse> => {
  const response = await axios.delete<DeleteLocationResponse>(
    `/api/private/location/${id}/delete`,
  );
  return response.data;
};
