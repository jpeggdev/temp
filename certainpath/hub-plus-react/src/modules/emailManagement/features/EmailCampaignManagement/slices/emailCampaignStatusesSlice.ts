import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { EmailCampaignStatus } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";
import { FetchEmailCampaignsRequest } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaigns/types";
import { fetchEmailCampaignStatuses } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaignStatuses/fetchEmailCampaignStatusesApi";
import { FetchEmailCampaignStatusesResponse } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaignStatuses/types";

interface EmailCampaignStatusesState {
  emailCampaignStatuses: EmailCampaignStatus[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: EmailCampaignStatusesState = {
  emailCampaignStatuses: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const emailCampaignStatusesSlice = createSlice({
  name: "emailCampaignStatuses",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setEmailCampaignStatusesData: (
      state,
      action: PayloadAction<{
        emailCampaignStatuses: EmailCampaignStatus[];
        totalCount: number;
      }>,
    ) => {
      state.emailCampaignStatuses = action.payload.emailCampaignStatuses;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setEmailCampaignStatusesData } =
  emailCampaignStatusesSlice.actions;

export const fetchEmailCampaignStatusesAction =
  (requestData: FetchEmailCampaignsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchEmailCampaignStatusesResponse =
        await fetchEmailCampaignStatuses(requestData);
      dispatch(
        setEmailCampaignStatusesData({
          emailCampaignStatuses: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch email campaign statuses"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default emailCampaignStatusesSlice.reducer;
