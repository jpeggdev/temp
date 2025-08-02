import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  StochasticClientMailDataRow,
  FetchStochasticClientMailDataRequest,
  FetchStochasticClientMailDataResponse,
} from "../../../../../api/fetchStochasticClientMailData/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { fetchStochasticClientMailData } from "../../../../../api/fetchStochasticClientMailData/fetchStochasticClientMailDataApi";

interface StochasticMailingState {
  mailDataRows: StochasticClientMailDataRow[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: StochasticMailingState = {
  mailDataRows: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const stochasticMailingSlice = createSlice({
  name: "stochasticMailing",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setMailData: (
      state,
      action: PayloadAction<{
        mailDataRows: StochasticClientMailDataRow[];
        totalCount: number;
      }>,
    ) => {
      state.mailDataRows = action.payload.mailDataRows;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setMailData } =
  stochasticMailingSlice.actions;

/**
 * Thunk action to fetch Stochastic Mailing Data from the endpoint.
 * The endpoint now returns { data: StochasticClientMailDataRow[], meta?: { totalCount?: number } }
 */
export const fetchStochasticMailDataAction =
  (requestParams: FetchStochasticClientMailDataRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchStochasticClientMailDataResponse =
        await fetchStochasticClientMailData(requestParams);

      // response.data is already an array of StochasticClientMailDataRow
      dispatch(
        setMailData({
          mailDataRows: response.data,
          totalCount: response.meta?.totalCount ?? 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch stochastic client mail data"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default stochasticMailingSlice.reducer;
