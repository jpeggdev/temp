import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { EmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";
import {
  FetchEmailCampaignsRequest,
  FetchEmailCampaignsResponse,
} from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaigns/types";
import { fetchEmailCampaigns } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaigns/fetchEmailCampaignsApi";

interface EmailCampaignListState {
  emailCampaigns: EmailCampaign[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: EmailCampaignListState = {
  emailCampaigns: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const emailCampaignListSlice = createSlice({
  name: "emailCampaignList",
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
        emailCampaigns: EmailCampaign[];
        totalCount: number;
      }>,
    ) => {
      state.emailCampaigns = action.payload.emailCampaigns;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setEmailCampaignsData } =
  emailCampaignListSlice.actions;

export const fetchEmailCampaignsAction =
  (requestData: FetchEmailCampaignsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchEmailCampaignsResponse =
        await fetchEmailCampaigns(requestData);
      dispatch(
        setEmailCampaignsData({
          emailCampaigns: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch email campaigns"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default emailCampaignListSlice.reducer;
