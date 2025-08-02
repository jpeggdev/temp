import axios from "@/api/axiosInstance";
import { GetCreateUpdateEmailTemplateCategoryMetadataResponse } from "./types";

export const getCreateUpdateEmailTemplateCategoryMetadata =
  async (): Promise<GetCreateUpdateEmailTemplateCategoryMetadataResponse> => {
    const response =
      await axios.get<GetCreateUpdateEmailTemplateCategoryMetadataResponse>(
        "/api/private/email-template-category/create-update-metadata",
      );
    return response.data;
  };
