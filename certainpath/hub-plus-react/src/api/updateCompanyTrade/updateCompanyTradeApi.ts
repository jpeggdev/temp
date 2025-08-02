import axios from "../axiosInstance";
import { UpdateCompanyTradeDTO, UpdateCompanyTradeResponse } from "./types";

export const updateCompanyTrade = async (
  uuid: string,
  updateCompanyTradeDTO: UpdateCompanyTradeDTO,
): Promise<UpdateCompanyTradeResponse> => {
  const response = await axios.put<UpdateCompanyTradeResponse>(
    `/api/private/companies/${uuid}/trades`,
    updateCompanyTradeDTO,
  );
  return response.data;
};
