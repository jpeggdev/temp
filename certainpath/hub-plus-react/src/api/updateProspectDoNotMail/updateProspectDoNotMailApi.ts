import axios from "../axiosInstance";
import {
  UpdateProspectDoNotMailRequest,
  UpdateProspectDoNotMailResponse,
} from "./types";

export const updateProspectDoNotMail = async (
  prospectId: number,
  requestData: UpdateProspectDoNotMailRequest,
): Promise<UpdateProspectDoNotMailResponse> => {
  const response = await axios.patch<UpdateProspectDoNotMailResponse>(
    `/api/private/stochastic-prospects/${prospectId}/do-not-mail`,
    requestData,
  );
  return response.data;
};
