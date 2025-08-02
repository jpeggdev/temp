import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateVoucherRequest,
  CreateVoucherResponse,
  Voucher,
} from "@/modules/eventRegistration/features/EventVoucherManagement/api/createVoucher/types";
import { createVoucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/createVoucher/createVoucherApi";
import { fetchVoucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/fetchVoucher/fetchVoucherApi";
import {
  EditVoucherRequest,
  EditVoucherResponse,
} from "@/modules/eventRegistration/features/EventVoucherManagement/api/editVoucher/types";
import { updateVoucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/editVoucher/editVoucherApi";
import { deleteVoucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/deleteVoucher/deleteVoucherApi";

interface VoucherState {
  createdVoucher: Voucher | null;
  loadingCreate: boolean;
  errorCreate: string | null;

  fetchedVoucher: Voucher | null;
  loadingFetch: boolean;
  errorFetch: string | null;

  updatedVoucher: Voucher | null;
  loadingUpdate: boolean;
  errorUpdate: string | null;

  loadingDelete: boolean;
  errorDelete: string | null;
}

const initialState: VoucherState = {
  createdVoucher: null,
  loadingCreate: false,
  errorCreate: null,

  fetchedVoucher: null,
  loadingFetch: false,
  errorFetch: null,

  updatedVoucher: null,
  loadingUpdate: false,
  errorUpdate: null,

  loadingDelete: false,
  errorDelete: null,
};

const voucherSlice = createSlice({
  name: "voucher",
  initialState,
  reducers: {
    setCreatedVoucher: (
      state,
      action: PayloadAction<{
        data: Voucher;
      }>,
    ) => {
      state.createdVoucher = action.payload.data;
    },
    setLoadingCreate: (state, action: PayloadAction<boolean>) => {
      state.loadingCreate = action.payload;
    },
    setErrorCreate: (state, action: PayloadAction<string | null>) => {
      state.errorCreate = action.payload;
    },
    setFetchedVoucher: (
      state,
      action: PayloadAction<{
        data: Voucher;
      }>,
    ) => {
      state.fetchedVoucher = action.payload.data;
    },
    setLoadingFetch: (state, action: PayloadAction<boolean>) => {
      state.loadingFetch = action.payload;
    },
    setErrorFetch: (state, action: PayloadAction<string | null>) => {
      state.errorFetch = action.payload;
    },
    setUpdatedVoucher: (
      state,
      action: PayloadAction<{
        data: Voucher;
      }>,
    ) => {
      state.updatedVoucher = action.payload.data;
    },
    setLoadingUpdate: (state, action: PayloadAction<boolean>) => {
      state.loadingUpdate = action.payload;
    },
    setErrorUpdate: (state, action: PayloadAction<string | null>) => {
      state.errorUpdate = action.payload;
    },
    setLoadingDelete: (state, action: PayloadAction<boolean>) => {
      state.loadingDelete = action.payload;
    },
    setErrorDelete: (state, action: PayloadAction<string | null>) => {
      state.errorDelete = action.payload;
    },
  },
});

export const {
  setLoadingCreate,
  setErrorCreate,
  setCreatedVoucher,
  setFetchedVoucher,
  setLoadingFetch,
  setErrorFetch,
  setUpdatedVoucher,
  setLoadingUpdate,
  setErrorUpdate,
  setLoadingDelete,
  setErrorDelete,
} = voucherSlice.actions;

export const createVoucherAction =
  (
    requestData: CreateVoucherRequest,
    onSuccess?: (createdData: CreateVoucherResponse["data"]) => void,
  ) =>
  async (dispatch: AppDispatch): Promise<void> => {
    dispatch(setLoadingCreate(true));
    dispatch(setErrorCreate(null));

    try {
      try {
        const response = await createVoucher(requestData);
        dispatch(setCreatedVoucher(response));
        if (onSuccess) {
          onSuccess(response.data);
        }
      } catch (error) {
        const message =
          error instanceof Error ? error.message : "Failed to create voucher.";
        dispatch(setErrorCreate(message));
        throw error;
      }
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const fetchVoucherAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetch(true));
    try {
      const response = await fetchVoucher(id);
      dispatch(setFetchedVoucher(response));
    } catch (error) {
      dispatch(
        setErrorFetch(
          error instanceof Error
            ? error.message
            : "Failed to fetch the voucher.",
        ),
      );
    } finally {
      dispatch(setLoadingFetch(false));
    }
  };

export const updateVoucherAction =
  (
    id: number,
    requestData: EditVoucherRequest,
    onSuccess?: (updatedData: EditVoucherResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setErrorUpdate(null));
    try {
      const response = await updateVoucher(id, requestData);
      dispatch(setUpdatedVoucher(response));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update the voucher.";
      dispatch(setErrorUpdate(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteVoucherAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    try {
      await deleteVoucher(id);
    } catch (error) {
      dispatch(
        setErrorDelete(
          error instanceof Error
            ? error.message
            : "Failed to delete the voucher.",
        ),
      );
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export default voucherSlice.reducer;
