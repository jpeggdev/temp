import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  FetchUserAppSettingsResponse,
  UserAppSettings,
} from "../../../../../api/fetchUserAppSettings/types";
import { fetchUserAppSettings } from "../../../../../api/fetchUserAppSettings/fetchUserAppSettingsApi";
import { AppDispatch, AppThunk } from "../../../../../app/store";

interface UserAppSettingsState {
  userAppSettings: UserAppSettings | null;
  loading: boolean;
  error: string | null;
}

const initialState: UserAppSettingsState = {
  userAppSettings: null,
  loading: false,
  error: null,
};

const userAppSettingsSlice = createSlice({
  name: "userAppSettings",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setUserAppSettings: (state, action: PayloadAction<UserAppSettings>) => {
      state.userAppSettings = action.payload;
    },
  },
});

export const { setLoading, setError, setUserAppSettings } =
  userAppSettingsSlice.actions;

export const fetchUserAppSettingsAction =
  (shouldSetLoading = true): AppThunk =>
  async (dispatch: AppDispatch) => {
    if (shouldSetLoading) {
      dispatch(setLoading(true));
    }

    try {
      const response: FetchUserAppSettingsResponse =
        await fetchUserAppSettings();
      dispatch(setUserAppSettings(response.data));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch user app settings"));
      }
    } finally {
      if (shouldSetLoading) {
        dispatch(setLoading(false));
      }
    }
  };

export default userAppSettingsSlice.reducer;
