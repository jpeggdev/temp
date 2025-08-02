import axios from "../../../../../../api/axiosInstance";
import {
  uploadDoNotMailRequest,
  uploadDoNotMailResponse,
} from "@/modules/stochastic/features/DoNotMailImport/api/uploadDoNotMailList/types";

export const uploadDoNotMailList = async (
  requestData: uploadDoNotMailRequest,
  onUploadProgress?: (progress: number) => void,
): Promise<uploadDoNotMailResponse> => {
  const formData = new FormData();
  formData.append("file", requestData.file);

  const response = await axios.post<uploadDoNotMailResponse>(
    "/api/private/stochastic/do-not-mail-list/upload",
    formData,
    {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress: (event) => {
        if (event.total) {
          const percent = Math.round((event.loaded * 100) / event.total);
          if (onUploadProgress) {
            onUploadProgress(percent);
          }
        }
      },
    },
  );

  return response.data;
};
