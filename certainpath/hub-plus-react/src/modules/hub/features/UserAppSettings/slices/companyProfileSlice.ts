import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { MyCompanyProfile } from "../../../../../api/getMyCompanyProfile/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { getMyCompanyProfile } from "../../../../../api/getMyCompanyProfile/getMyCompanyProfileApi";
import { UpdateMyCompanyProfileRequest } from "../../../../../api/updateMyCompanyProfile/types";
import { updateMyCompanyProfile } from "../../../../../api/updateMyCompanyProfile/updateMyCompanyProfileApi";

interface CompanyProfileState {
  companyProfile: MyCompanyProfile | null;
  loading: boolean;
  error: string | null;
}

const initialState: CompanyProfileState = {
  companyProfile: null,
  loading: false,
  error: null,
};

const companyProfileSlice = createSlice({
  name: "companyProfile",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setCompanyProfile: (state, action: PayloadAction<MyCompanyProfile>) => {
      state.companyProfile = action.payload;
    },
  },
});

export const { setLoading, setError, setCompanyProfile } =
  companyProfileSlice.actions;

export const fetchCompanyProfileAction =
  (shouldSetLoading = true): AppThunk =>
  async (dispatch: AppDispatch) => {
    if (shouldSetLoading) {
      dispatch(setLoading(true));
    }

    try {
      const response = await getMyCompanyProfile();
      dispatch(setCompanyProfile(response.data));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch company profile"));
      }
    } finally {
      if (shouldSetLoading) {
        dispatch(setLoading(false));
      }
    }
  };

/**
 * Update the company profile and execute a callback on success.
 *
 * @param profileData - The data to update the company profile with.
 * @param callback - Optional callback to execute after a successful update.
 */
export const updateCompanyProfileAction =
  (
    profileData: UpdateMyCompanyProfileRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));

    try {
      await updateMyCompanyProfile(profileData);
      dispatch(fetchCompanyProfileAction(false));

      if (callback) {
        callback();
      }
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to update company profile"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default companyProfileSlice.reducer;
