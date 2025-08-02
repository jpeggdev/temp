import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  Address,
  uploadDoNotMailRequest,
} from "@/modules/stochastic/features/DoNotMailImport/api/uploadDoNotMailList/types";
import { uploadDoNotMailList } from "@/modules/stochastic/features/DoNotMailImport/api/uploadDoNotMailList/uploadDoNotMailListApi";
import { bulkCreateRestrictedAddressesRequest } from "@/modules/stochastic/features/DoNotMailImport/api/bulkCreateRestrictedAddresses/types";
import { bulkCreateRestrictedAddresses } from "@/modules/stochastic/features/DoNotMailImport/api/bulkCreateRestrictedAddresses/bulkCreateRestrictedAddressesApi";

interface DoNotMailImportState {
  addressesMatches: Address[] | null;
  matchesCount: number;
  loadingUpload: boolean;
  errorUpload: string | null;
  uploadProgress: number;
  createRestrictedAddressesResponse: string | null;
  loadingCreate: boolean;
}

const initialState: DoNotMailImportState = {
  addressesMatches: null,
  matchesCount: 0,
  loadingUpload: false,
  errorUpload: null,
  uploadProgress: 0,
  createRestrictedAddressesResponse: null,
  loadingCreate: false,
};

const doNotMailListImportSlice = createSlice({
  name: "doNotMailImport",
  initialState,
  reducers: {
    setUploadedRestrictedAddresses: (
      state,
      action: PayloadAction<{
        data: {
          addresses: Address[];
          matchesCount: number;
        };
      }>,
    ) => {
      state.addressesMatches = action.payload.data.addresses;
      state.matchesCount = action.payload.data.matchesCount;
    },
    setLoadingUpload: (state, action: PayloadAction<boolean>) => {
      state.loadingUpload = action.payload;
    },
    setErrorUpload: (state, action: PayloadAction<string | null>) => {
      state.errorUpload = action.payload;
    },
    resetUploadedRestrictedAddresses: (state) => {
      state.addressesMatches = null;
    },
    setUploadProgress: (state, action: PayloadAction<number>) => {
      state.uploadProgress = action.payload;
    },
    setCreateRestrictedAddressesResponse: (
      state,
      action: PayloadAction<{
        message: string;
      }>,
    ) => {
      state.createRestrictedAddressesResponse = action.payload.message;
    },
    setLoadingCreate: (state, action: PayloadAction<boolean>) => {
      state.loadingCreate = action.payload;
    },
  },
});

export const {
  setUploadedRestrictedAddresses,
  setLoadingUpload,
  setErrorUpload,
  resetUploadedRestrictedAddresses,
  setUploadProgress,
  setCreateRestrictedAddressesResponse,
  setLoadingCreate,
} = doNotMailListImportSlice.actions;

export const uploadDoNotMailListAction =
  (requestData: uploadDoNotMailRequest, callback?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(resetUploadedRestrictedAddresses());
    dispatch(setLoadingUpload(true));
    dispatch(setErrorUpload(null));
    dispatch(setUploadProgress(0));

    try {
      const response = await uploadDoNotMailList(requestData, (progress) => {
        dispatch(setUploadProgress(progress));
      });

      dispatch(setUploadedRestrictedAddresses(response));

      if (callback) callback();
    } catch (error) {
      dispatch(
        setErrorUpload(
          error instanceof Error
            ? error.message
            : "Failed to upload the do not mail file.",
        ),
      );
      throw error;
    } finally {
      dispatch(setLoadingUpload(false));
    }
  };

export const addToDoNotMailListAction =
  (
    requestData: bulkCreateRestrictedAddressesRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setUploadProgress(0));
    dispatch(setLoadingCreate(true));

    try {
      const response = await bulkCreateRestrictedAddresses(requestData);

      dispatch(setCreateRestrictedAddressesResponse(response));

      if (callback) callback();
    } catch (error) {
      dispatch(
        setErrorUpload(
          error instanceof Error
            ? error.message
            : "Failed to add addresses to the do not mail list.",
        ),
      );
    } finally {
      dispatch(setLoadingUpload(false));
    }
  };

export const resetRestrictedAddressesAction =
  (): AppThunk => (dispatch: AppDispatch) => {
    try {
      dispatch(resetUploadedRestrictedAddresses());
      dispatch(setLoadingUpload(false));
      dispatch(setErrorUpload(null));
      dispatch(setUploadProgress(0));
      dispatch(setLoadingCreate(false));
    } catch (error) {
      dispatch(
        setErrorUpload(
          error instanceof Error
            ? error.message
            : "Failed to reset restricted addresses.",
        ),
      );
    }
  };

export default doNotMailListImportSlice.reducer;
