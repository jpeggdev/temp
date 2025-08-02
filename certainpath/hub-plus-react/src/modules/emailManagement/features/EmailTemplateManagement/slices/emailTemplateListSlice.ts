import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  FetchEmailTemplatesRequest,
  FetchEmailTemplatesResponse,
} from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplates/types";
import { fetchEmailTemplates } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplates/fetchEmailTemplatesApi";
import { EmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/types";

interface EmailTemplatesListState {
  emailTemplates: EmailTemplate[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: EmailTemplatesListState = {
  emailTemplates: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const emailTemplateListSlice = createSlice({
  name: "emailTemplateList",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setEmailCampaignsData: (
      state,
      action: PayloadAction<{
        emailTemplates: EmailTemplate[];
        totalCount: number;
      }>,
    ) => {
      state.emailTemplates = action.payload.emailTemplates;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setEmailCampaignsData } =
  emailTemplateListSlice.actions;

export const fetchEmailTemplatesAction =
  (requestData: FetchEmailTemplatesRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchEmailTemplatesResponse =
        await fetchEmailTemplates(requestData);
      dispatch(
        setEmailCampaignsData({
          emailTemplates: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch email templates"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default emailTemplateListSlice.reducer;
