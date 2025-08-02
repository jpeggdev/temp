import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { MyUserProfile } from "../../../../../api/getMyUserProfile/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { getMyUserProfile } from "../../../../../api/getMyUserProfile/getMyUserProfileApi";
import { UpdateMyUserProfileRequest } from "../../../../../api/updateMyUserProfile/types";
import { updateMyUserProfile } from "../../../../../api/updateMyUserProfile/updateMyUserProfileApi";

interface UserProfileState {
  userProfile: MyUserProfile | null;
  loading: boolean;
  saving: boolean;
  error: string | null;
}

const initialState: UserProfileState = {
  userProfile: null,
  loading: false,
  saving: false,
  error: null,
};

const userProfileSlice = createSlice({
  name: "userProfile",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setSaving: (state, action: PayloadAction<boolean>) => {
      state.saving = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setUserProfile: (state, action: PayloadAction<MyUserProfile>) => {
      state.userProfile = action.payload;
    },
  },
});

export const { setLoading, setSaving, setError, setUserProfile } =
  userProfileSlice.actions;

export const fetchUserProfileAction =
  (shouldSetLoading = true): AppThunk =>
  async (dispatch: AppDispatch) => {
    if (shouldSetLoading) {
      dispatch(setLoading(true));
    }

    try {
      const response = await getMyUserProfile();
      dispatch(setUserProfile(response.data));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch user profile"));
      }
    } finally {
      if (shouldSetLoading) {
        dispatch(setLoading(false));
      }
    }
  };

/**
 * Update the user profile and execute a callback on success.
 *
 * @param profileData - The data to update the user profile with.
 * @param callback - Optional callback to execute after a successful update.
 */
export const updateUserProfileAction =
  (profileData: UpdateMyUserProfileRequest, callback?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSaving(true));

    try {
      await updateMyUserProfile(profileData);
      dispatch(fetchUserProfileAction(false));

      if (callback) {
        callback();
      }
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to update user profile"));
      }
    } finally {
      dispatch(setSaving(false));
    }
  };

export default userProfileSlice.reducer;
