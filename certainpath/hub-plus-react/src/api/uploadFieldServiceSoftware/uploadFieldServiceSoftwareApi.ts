import axios from "../axiosInstance";
import { AxiosProgressEvent } from "axios";
import {
  UploadFieldServiceSoftwareDTO,
  UploadFieldServiceSoftwareResponse,
} from "./types";

export const uploadFieldServiceSoftware = async (
  uploadFieldServiceSoftwareDTO: UploadFieldServiceSoftwareDTO,
  onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
): Promise<UploadFieldServiceSoftwareResponse> => {
  const formData = new FormData();
  formData.append("file", uploadFieldServiceSoftwareDTO.file);
  formData.append("software", uploadFieldServiceSoftwareDTO.software);
  formData.append("trade", uploadFieldServiceSoftwareDTO.trade);
  formData.append("importType", uploadFieldServiceSoftwareDTO.importType);
  if (
    "jobs" === uploadFieldServiceSoftwareDTO.importType ||
    "invoices" === uploadFieldServiceSoftwareDTO.importType
  ) {
    formData.append("isJobsOrInvoiceFile", "true");
  } else if ("activeCustomers" === uploadFieldServiceSoftwareDTO.importType) {
    formData.append("isMemberFile", "true");
  } else if (
    "activeCustomersClub" === uploadFieldServiceSoftwareDTO.importType
  ) {
    formData.append("isActiveClubMemberFile", "true");
  }

  const response = await axios.post<UploadFieldServiceSoftwareResponse>(
    `/api/private/stochastic-field-service/upload`,
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
