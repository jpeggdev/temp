import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  EmployeeRole,
  GetResourceLibraryMetadataResponse,
  ResourceCategory,
  ResourceType,
  Trade,
} from "@/modules/hub/features/ResourceLibrary/api/getResourceLibratyMetadata/types";
import { getResourceLibraryMetadata } from "@/modules/hub/features/ResourceLibrary/api/getResourceLibratyMetadata/getResourceLibraryMetadataApi";

interface ResourceLibraryMetadataState {
  metadata: {
    trades: Trade[];
    resourceTypes: ResourceType[];
    resourceCategories: ResourceCategory[];
    employeeRoles: EmployeeRole[];
  } | null;
  loadingGet: boolean;
  errorGet: string | null;
}

const initialState: ResourceLibraryMetadataState = {
  metadata: null,
  loadingGet: false,
  errorGet: null,
};

const resourceLibraryMetadataSlice = createSlice({
  name: "resourceLibraryMetadata",
  initialState,
  reducers: {
    setMetadata: (
      state,
      action: PayloadAction<GetResourceLibraryMetadataResponse>,
    ) => {
      const { trades, employeeRoles, resourceTypes, resourceCategories } =
        action.payload.data.filters;
      state.metadata = {
        trades,
        employeeRoles,
        resourceTypes,
        resourceCategories,
      };
    },
    setLoadingGet: (state, action: PayloadAction<boolean>) => {
      state.loadingGet = action.payload;
    },
    setErrorGet: (state, action: PayloadAction<string | null>) => {
      state.errorGet = action.payload;
    },
    resetFilters: () => initialState,
  },
});

export const { setMetadata, setLoadingGet, setErrorGet, resetFilters } =
  resourceLibraryMetadataSlice.actions;

export const getResourceLibraryMetadataAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setErrorGet(null));
    try {
      const response = await getResourceLibraryMetadata();
      dispatch(setMetadata(response));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to fetch metadata.";
      dispatch(setErrorGet(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export default resourceLibraryMetadataSlice.reducer;
