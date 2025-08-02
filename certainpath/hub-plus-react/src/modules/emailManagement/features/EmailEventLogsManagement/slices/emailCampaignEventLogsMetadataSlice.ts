import { AppDispatch, AppThunk } from "@/app/store";
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  EmailCampaignEventLogsMetadata,
  FetchEmailCampaignEventLogsMetadataRequest,
  FetchEmailCampaignEventLogsMetadataResponse,
} from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogMetadata/types";
import { fetchEmailCampaignEventLogsMetadata } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogMetadata/fetchEmailCampaignEventLogMetadataApi";

interface EmailCampaignEventLogsMetadataState {
  emailCampaignEventLogsMetadata: EmailCampaignEventLogsMetadata;
  loading: boolean;
  error: string | null;
}

const initialState: EmailCampaignEventLogsMetadataState = {
  emailCampaignEventLogsMetadata: {
    emailEventCount: {
      delivered: 0,
      opened: 0,
      clicked: 0,
      failed: 0,
    },
    emailEventRate: {
      delivered: 0,
      opened: 0,
      clicked: 0,
      failed: 0,
    },
  },
  loading: false,
  error: null,
};

const emailCampaignEventLogsMetadataSlice = createSlice({
  name: "emailCampaignEventLogsMetadata",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setEmailCampaignEventLogsMetadata: (
      state,
      action: PayloadAction<EmailCampaignEventLogsMetadata>,
    ) => {
      state.emailCampaignEventLogsMetadata = action.payload;
    },
  },
});

export const { setLoading, setError, setEmailCampaignEventLogsMetadata } =
  emailCampaignEventLogsMetadataSlice.actions;

export const fetchEmailCampaignEventLogsMetadataAction =
  (requestData: FetchEmailCampaignEventLogsMetadataRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchEmailCampaignEventLogsMetadataResponse =
        await fetchEmailCampaignEventLogsMetadata(requestData);
      dispatch(setEmailCampaignEventLogsMetadata(response.data));
      dispatch(setError(null));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch email campaign event logs metadata",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default emailCampaignEventLogsMetadataSlice.reducer;
