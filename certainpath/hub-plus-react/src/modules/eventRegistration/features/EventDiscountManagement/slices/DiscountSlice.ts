import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  Discount,
  CreateDiscountRequest,
  CreateDiscountResponse,
} from "@/modules/eventRegistration/features/EventDiscountManagement/api/createDiscount/types";
import { createDiscount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/createDiscount/createDiscountApi";
import { fetchDiscount } from "../api/fetchDiscount/fetchDiscountApi";
import {
  EditDiscountRequest,
  EditDiscountResponse,
} from "../api/editDiscount/types";
import { updateDiscount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/editDiscount/editDiscountApi";
import { deleteDiscount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/deleteDiscount/deleteDiscountApi";

interface DiscountState {
  createdDiscount: Discount | null;
  loadingCreate: boolean;
  errorCreate: string | null;

  updatedDiscount: Discount | null;
  loadingUpdate: boolean;
  errorUpdate: string | null;

  fetchedDiscount: Discount | null;
  loadingFetch: boolean;
  errorFetch: string | null;

  loadingDelete: boolean;
  errorDelete: string | null;
}

const initialState: DiscountState = {
  createdDiscount: null,
  loadingCreate: false,
  errorCreate: null,

  updatedDiscount: null,
  loadingUpdate: false,
  errorUpdate: null,

  fetchedDiscount: null,
  loadingFetch: false,
  errorFetch: null,

  loadingDelete: false,
  errorDelete: null,
};

const discountSlice = createSlice({
  name: "discount",
  initialState,
  reducers: {
    setCreatedDiscount: (
      state,
      action: PayloadAction<{
        data: Discount;
      }>,
    ) => {
      state.createdDiscount = action.payload.data;
    },
    setLoadingCreate: (state, action: PayloadAction<boolean>) => {
      state.loadingCreate = action.payload;
    },
    setErrorCreate: (state, action: PayloadAction<string | null>) => {
      state.errorCreate = action.payload;
    },
    setUpdatedDiscount: (
      state,
      action: PayloadAction<{
        data: Discount;
      }>,
    ) => {
      state.updatedDiscount = action.payload.data;
    },
    setLoadingUpdate: (state, action: PayloadAction<boolean>) => {
      state.loadingUpdate = action.payload;
    },
    setErrorUpdate: (state, action: PayloadAction<string | null>) => {
      state.errorUpdate = action.payload;
    },
    setFetchedDiscount: (
      state,
      action: PayloadAction<{
        data: Discount;
      }>,
    ) => {
      state.fetchedDiscount = action.payload.data;
    },
    setLoadingFetch: (state, action: PayloadAction<boolean>) => {
      state.loadingFetch = action.payload;
    },
    setErrorFetch: (state, action: PayloadAction<string | null>) => {
      state.errorFetch = action.payload;
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
  setCreatedDiscount,
  setLoadingDelete,
  setErrorDelete,
  setFetchedDiscount,
  setUpdatedDiscount,
  setLoadingUpdate,
  setErrorUpdate,
  setLoadingFetch,
  setErrorFetch,
} = discountSlice.actions;

export const createDiscountAction =
  (
    requestData: CreateDiscountRequest,
    onSuccess?: (createdData: CreateDiscountResponse["data"]) => void,
  ) =>
  async (dispatch: AppDispatch): Promise<void> => {
    dispatch(setLoadingCreate(true));
    dispatch(setErrorCreate(null));

    try {
      try {
        const response = await createDiscount(requestData);
        dispatch(setCreatedDiscount(response));
        if (onSuccess) {
          onSuccess(response.data);
        }
      } catch (error) {
        const message =
          error instanceof Error
            ? error.message
            : "Failed to create the discount.";
        dispatch(setErrorCreate(message));
        throw error;
      }
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const fetchDiscountAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetch(true));
    try {
      const response = await fetchDiscount(id);
      dispatch(setFetchedDiscount(response));
    } catch (error) {
      dispatch(
        setErrorFetch(
          error instanceof Error
            ? error.message
            : "Failed to fetch the discount.",
        ),
      );
    } finally {
      dispatch(setLoadingFetch(false));
    }
  };

export const updateDiscountAction =
  (
    id: number,
    requestData: EditDiscountRequest,
    onSuccess?: (updatedData: EditDiscountResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setErrorUpdate(null));
    try {
      const response = await updateDiscount(id, requestData);
      dispatch(setUpdatedDiscount(response));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update the discount.";
      dispatch(setErrorUpdate(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteDiscountAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    try {
      await deleteDiscount(id);
    } catch (error) {
      dispatch(
        setErrorDelete(
          error instanceof Error
            ? error.message
            : "Failed to delete the discount.",
        ),
      );
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export default discountSlice.reducer;
