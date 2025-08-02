import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  CreateUpdateResourceMetadata,
  FetchCreateUpdateResourceMetadataResponse,
} from "@/api/fetchCreateUpdateResourceMetadata/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { fetchCreateUpdateResourceMetadata } from "@/api/fetchCreateUpdateResourceMetadata/fetchCreateUpdateResourceMetadataApi";

interface CreateUpdateResourceMetadataState {
  resourceTags: CreateUpdateResourceMetadata["resourceTags"];
  resourceCategories: CreateUpdateResourceMetadata["resourceCategories"];
  employeeRoles: CreateUpdateResourceMetadata["employeeRoles"];
  trades: CreateUpdateResourceMetadata["trades"];
  resourceTypes: CreateUpdateResourceMetadata["resourceTypes"];
  loading: boolean;
  error: string | null;
}

const initialState: CreateUpdateResourceMetadataState = {
  resourceTags: [],
  resourceCategories: [],
  employeeRoles: [],
  trades: [],
  resourceTypes: [],
  loading: false,
  error: null,
};

const resourceManagementMetadataSlice = createSlice({
  name: "resourceManagementMetadataSlice",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setMetadata: (
      state,
      action: PayloadAction<CreateUpdateResourceMetadata>,
    ) => {
      state.resourceTags = action.payload.resourceTags;
      state.resourceCategories = action.payload.resourceCategories;
      state.employeeRoles = action.payload.employeeRoles;
      state.trades = action.payload.trades;
      state.resourceTypes = action.payload.resourceTypes;
    },
  },
});

export const { setLoading, setError, setMetadata } =
  resourceManagementMetadataSlice.actions;

export const getCreateUpdateResourceMetadataAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    dispatch(setError(null));

    try {
      const response: FetchCreateUpdateResourceMetadataResponse =
        await fetchCreateUpdateResourceMetadata();
      dispatch(setMetadata(response.data));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch Resource Metadata",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default resourceManagementMetadataSlice.reducer;
