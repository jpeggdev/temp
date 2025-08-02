import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  FetchQuickBooksReportsRequest,
  QuickBooksReport,
} from "../../../../../api/fetchQuickBooksReports/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { fetchQuickBooksReports } from "../../../../../api/fetchQuickBooksReports/fetchQuickBooksReportsApi";
import { downloadQuickBooksReport } from "../../../../../api/downloadQuickBooksReport/downloadQuickBooksReportApi";
import { DownloadQuickBooksReportRequest } from "../../../../../api/downloadQuickBooksReport/types";

interface QuickBooksReportsState {
  reports: QuickBooksReport[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: QuickBooksReportsState = {
  reports: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const quickBooksReportsSlice = createSlice({
  name: "quickBooksReports",
  initialState,
  reducers: {
    setLoading(state, action: PayloadAction<boolean>) {
      state.loading = action.payload;
    },
    setError(state, action: PayloadAction<string | null>) {
      state.error = action.payload;
    },
    setReports(
      state,
      action: PayloadAction<{
        reports: QuickBooksReport[];
        totalCount: number;
      }>,
    ) {
      state.reports = action.payload.reports;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setReports } =
  quickBooksReportsSlice.actions;

export const fetchQuickBooksReportsAction =
  (requestData: FetchQuickBooksReportsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response = await fetchQuickBooksReports(requestData);
      dispatch(
        setReports({
          reports: response.data,
          totalCount: response.meta?.totalCount ?? 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch reports"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export const downloadQuickBooksReportAction =
  (requestData: DownloadQuickBooksReportRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const responseBlob = await downloadQuickBooksReport(requestData);

      const downloadUrl = URL.createObjectURL(responseBlob);
      const link = document.createElement("a");
      link.href = downloadUrl;

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      URL.revokeObjectURL(downloadUrl);
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to download report"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default quickBooksReportsSlice.reducer;
