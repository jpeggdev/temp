import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  EmailCampaignEventLog,
  FetchEmailCampaignEventLogsRequest,
  FetchEmailCampaignEventLogsResponse,
} from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogs/types";
import { fetchEmailCampaignEventLogs } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogs/fetchEmailCampaignEventLogsApi";

interface EmailCampaignEventLogListState {
  emailCampaignEventLogs: EmailCampaignEventLog[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: EmailCampaignEventLogListState = {
  emailCampaignEventLogs: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const emailCampaignEventLogListSlice = createSlice({
  name: "emailCampaignEventLogList",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setEmailCampaignEventLogsData: (
      state,
      action: PayloadAction<{
        emailCampaignEventLogs: EmailCampaignEventLog[];
        totalCount: number;
      }>,
    ) => {
      state.emailCampaignEventLogs = action.payload.emailCampaignEventLogs;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setEmailCampaignEventLogsData } =
  emailCampaignEventLogListSlice.actions;

export const fetchEmailCampaignLogsAction =
  (requestData: FetchEmailCampaignEventLogsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchEmailCampaignEventLogsResponse =
        await fetchEmailCampaignEventLogs(requestData);
      dispatch(
        setEmailCampaignEventLogsData({
          emailCampaignEventLogs: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch email campaign event logs"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default emailCampaignEventLogListSlice.reducer;
