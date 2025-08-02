import axios from "../axiosInstance";
import { AxiosProgressEvent } from "axios";
import { UploadPostageExpenseResponse, UploadPostageExpenseDTO } from "./types";

export const uploadPostageExpense = async (
  uploadPostageExpenseDTO: UploadPostageExpenseDTO,
  onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
): Promise<UploadPostageExpenseResponse> => {
  const formData = new FormData();
  formData.append("vendor", uploadPostageExpenseDTO.vendor);
  formData.append("type", uploadPostageExpenseDTO.type);
  formData.append("file", uploadPostageExpenseDTO.file);

  const response = await axios.post<UploadPostageExpenseResponse>(
    `/api/private/stochastic-postage-expense/upload`,
    formData,
    {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress,
    },
  );

  return response.data;
};
