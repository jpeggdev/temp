import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { getResources } from "@/api/getResources/getResourcesApi";
import { setPublishedResource } from "@/api/setPublishedResource/setPublishedResourceApi";
import { setFeaturedResource } from "@/api/setFeaturedResource/setFeaturedResourceApi";
import { getResourceFilterMetaData } from "@/api/getResourceFilterMetaData/getResourceFilterMetaDataApi";
import { deleteResource } from "@/api/deleteResource/deleteResourceApi";

export interface ResourceItem {
  id: number;
  uuid: string;
  title: string;
  isPublished: boolean;
  thumbnailUrl?: string | null;
  isFeatured: boolean; // newly added
  resourceType?: string | null; // newly added
  createdAt?: string | null;
}

interface ResourceFilterMetadataItem {
  id: number;
  name: string;
}

interface ResourceListState {
  resources: ResourceItem[];
  totalCount: number;

  fetchLoading: boolean;
  fetchError: string | null;

  setPublishedLoading: boolean;
  setPublishedError: string | null;

  setFeaturedLoading: boolean;
  setFeaturedError: string | null;

  deleteLoading: boolean;
  deleteError: string | null;

  filterMetadataLoading: boolean;
  filterMetadataError: string | null;
  resourceTypes: ResourceFilterMetadataItem[];
  employeeRoles: ResourceFilterMetadataItem[];
  trades: ResourceFilterMetadataItem[];
}

const initialState: ResourceListState = {
  resources: [],
  totalCount: 0,

  fetchLoading: false,
  fetchError: null,

  setPublishedLoading: false,
  setPublishedError: null,

  setFeaturedLoading: false,
  setFeaturedError: null,

  deleteLoading: false,
  deleteError: null,

  filterMetadataLoading: false,
  filterMetadataError: null,
  resourceTypes: [],
  employeeRoles: [],
  trades: [],
};

export const resourceListSlice = createSlice({
  name: "resourceList",
  initialState,
  reducers: {
    setFetchLoading(state, action: PayloadAction<boolean>) {
      state.fetchLoading = action.payload;
    },
    setFetchError(state, action: PayloadAction<string | null>) {
      state.fetchError = action.payload;
    },
    setResources(state, action: PayloadAction<ResourceItem[]>) {
      state.resources = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },
    setSetPublishedLoading(state, action: PayloadAction<boolean>) {
      state.setPublishedLoading = action.payload;
    },
    setSetPublishedError(state, action: PayloadAction<string | null>) {
      state.setPublishedError = action.payload;
    },
    updateResourcePublishedStatus(
      state,
      action: PayloadAction<{ uuid: string; isPublished: boolean }>,
    ) {
      const { uuid, isPublished } = action.payload;
      const index = state.resources.findIndex((r) => r.uuid === uuid);
      if (index !== -1) {
        state.resources[index].isPublished = isPublished;
      }
    },

    // ------ featured ------
    setSetFeaturedLoading(state, action: PayloadAction<boolean>) {
      state.setFeaturedLoading = action.payload;
    },
    setSetFeaturedError(state, action: PayloadAction<string | null>) {
      state.setFeaturedError = action.payload;
    },
    updateResourceFeaturedStatus(
      state,
      action: PayloadAction<{ uuid: string; isFeatured: boolean }>,
    ) {
      const { uuid, isFeatured } = action.payload;
      const index = state.resources.findIndex((r) => r.uuid === uuid);
      if (index !== -1) {
        state.resources[index].isFeatured = isFeatured;
      }
    },
    setDeleteLoading(state, action: PayloadAction<boolean>) {
      state.deleteLoading = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    removeResource(state, action: PayloadAction<string>) {
      state.resources = state.resources.filter(
        (r) => r.uuid !== action.payload,
      );
    },
    setFilterMetadataLoading(state, action: PayloadAction<boolean>) {
      state.filterMetadataLoading = action.payload;
    },
    setFilterMetadataError(state, action: PayloadAction<string | null>) {
      state.filterMetadataError = action.payload;
    },
    setResourceTypes(
      state,
      action: PayloadAction<ResourceFilterMetadataItem[]>,
    ) {
      state.resourceTypes = action.payload;
    },
    setEmployeeRoles(
      state,
      action: PayloadAction<ResourceFilterMetadataItem[]>,
    ) {
      state.employeeRoles = action.payload;
    },
    setTrades(state, action: PayloadAction<ResourceFilterMetadataItem[]>) {
      state.trades = action.payload;
    },
  },
});

export default resourceListSlice.reducer;

export const {
  setFetchLoading,
  setFetchError,
  setResources,
  setTotalCount,

  setSetPublishedLoading,
  setSetPublishedError,
  updateResourcePublishedStatus,

  setSetFeaturedLoading,
  setSetFeaturedError,
  updateResourceFeaturedStatus,

  setDeleteLoading,
  setDeleteError,
  removeResource,

  setFilterMetadataLoading,
  setFilterMetadataError,
  setResourceTypes,
  setEmployeeRoles,
  setTrades,
} = resourceListSlice.actions;

export const fetchResourcesAction =
  (params: Parameters<typeof getResources>[0]): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setFetchLoading(true));
    dispatch(setFetchError(null));
    try {
      const response = await getResources(params);
      dispatch(setResources(response.data));
      dispatch(setTotalCount(response.meta?.totalCount ?? 0));
    } catch (err) {
      dispatch(
        setFetchError(
          err instanceof Error ? err.message : "Failed to fetch resources.",
        ),
      );
    } finally {
      dispatch(setFetchLoading(false));
    }
  };

export const setPublishedResourceAction =
  (uuid: string, isPublished: boolean): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSetPublishedLoading(true));
    dispatch(setSetPublishedError(null));
    try {
      const response = await setPublishedResource(uuid, { isPublished });
      dispatch(
        updateResourcePublishedStatus({
          uuid: response.data.uuid,
          isPublished: response.data.isPublished,
        }),
      );
    } catch (err) {
      dispatch(
        setSetPublishedError(
          err instanceof Error ? err.message : "Failed to set published.",
        ),
      );
    } finally {
      dispatch(setSetPublishedLoading(false));
    }
  };

export const setFeaturedResourceAction =
  (uuid: string, isFeatured: boolean): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSetFeaturedLoading(true));
    dispatch(setSetFeaturedError(null));
    try {
      const response = await setFeaturedResource(uuid, { isFeatured });
      dispatch(
        updateResourceFeaturedStatus({
          uuid: response.data.uuid,
          isFeatured: response.data.isFeatured,
        }),
      );
    } catch (err) {
      dispatch(
        setSetFeaturedError(
          err instanceof Error ? err.message : "Failed to set featured.",
        ),
      );
    } finally {
      dispatch(setSetFeaturedLoading(false));
    }
  };

export const getResourceFilterMetaDataAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setFilterMetadataLoading(true));
    dispatch(setFilterMetadataError(null));
    try {
      const response = await getResourceFilterMetaData();
      dispatch(setResourceTypes(response.data.resourceTypes));
      dispatch(setEmployeeRoles(response.data.employeeRoles));
      dispatch(setTrades(response.data.trades));
    } catch (err) {
      dispatch(
        setFilterMetadataError(
          err instanceof Error
            ? err.message
            : "Failed to fetch resource filter metadata.",
        ),
      );
    } finally {
      dispatch(setFilterMetadataLoading(false));
    }
  };

export const deleteResourceAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    dispatch(setDeleteError(null));

    try {
      await deleteResource(uuid);
      dispatch(removeResource(uuid));
    } catch (err) {
      dispatch(
        setDeleteError(
          err instanceof Error ? err.message : "Failed to delete resource.",
        ),
      );
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };
