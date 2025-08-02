import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  GetResourceSearchResultsAPIResponse,
  GetResourceSearchResultsItem,
  GetResourceSearchResultsRequest,
  ResourceTypeFacet,
} from "@/api/getResourceSearchResults/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { getResourceSearchResults } from "@/api/getResourceSearchResults/getResourceSearchResultsApi";
import {
  Trade,
  EmployeeRole,
} from "@/modules/hub/features/ResourceLibrary/api/getResourceLibratyMetadata/types";
import { ResourceCategory } from "@/modules/hub/features/ResourceLibrary/api/getResourceLibratyMetadata/types";

interface ResourceLibraryState {
  resources: GetResourceSearchResultsItem[];
  resourceTypes: ResourceTypeFacet[];
  page: number;
  hasMore: boolean;
  searchInput: string;
  showOnlyFavorites: boolean;
  selectedResourceType: number | null;
  selectedTag: string | null;
  scrollPosition: number;
  isRestoringState: boolean;
  lastFetchParams: {
    searchTerm?: string;
    showFavorites?: boolean;
    resourceTypeIds?: number[];
    tradeIds?: number[];
    resourceCategoryIds?: number[];
    employeeRoleIds?: number[];
    page?: number;
    pageSize?: number;
  } | null;

  selectedResourceTypes: ResourceTypeFacet[];
  selectedTrades: Trade[];
  selectedResourceCategories: ResourceCategory[];
  selectedEmployeeRoles: EmployeeRole[];
}

const initialState: ResourceLibraryState = {
  resources: [],
  resourceTypes: [],
  page: 1,
  hasMore: true,
  searchInput: "",
  showOnlyFavorites: false,
  selectedResourceType: null,
  selectedTag: null,
  scrollPosition: 0,
  isRestoringState: false,
  lastFetchParams: null,
  selectedResourceTypes: [],
  selectedTrades: [],
  selectedResourceCategories: [],
  selectedEmployeeRoles: [],
};

const resourceLibrarySlice = createSlice({
  name: "resourceLibrary",
  initialState,
  reducers: {
    setResources: (
      state,
      action: PayloadAction<GetResourceSearchResultsItem[]>,
    ) => {
      state.resources = action.payload;
    },
    appendResources: (
      state,
      action: PayloadAction<GetResourceSearchResultsItem[]>,
    ) => {
      state.resources = [...state.resources, ...action.payload];
    },
    setResourceTypes: (state, action: PayloadAction<ResourceTypeFacet[]>) => {
      state.resourceTypes = action.payload;
    },
    setPage: (state, action: PayloadAction<number>) => {
      state.page = action.payload;
    },
    setHasMore: (state, action: PayloadAction<boolean>) => {
      state.hasMore = action.payload;
    },
    setSearchInput: (state, action: PayloadAction<string>) => {
      state.searchInput = action.payload;
    },
    setShowOnlyFavorites: (state, action: PayloadAction<boolean>) => {
      state.showOnlyFavorites = action.payload;
    },
    setScrollPosition: (state, action: PayloadAction<number>) => {
      state.scrollPosition = action.payload;
    },
    setIsRestoringState: (state, action: PayloadAction<boolean>) => {
      state.isRestoringState = action.payload;
    },
    setLastFetchParams: (
      state,
      action: PayloadAction<ResourceLibraryState["lastFetchParams"]>,
    ) => {
      state.lastFetchParams = action.payload;
    },
    setSelectedResourceTypes: (
      state,
      action: PayloadAction<ResourceTypeFacet[]>,
    ) => {
      state.selectedResourceTypes = action.payload;
    },
    setSelectedTrades: (state, action: PayloadAction<Trade[]>) => {
      state.selectedTrades = action.payload;
    },
    setSelectedResourceCategories: (
      state,
      action: PayloadAction<ResourceCategory[]>,
    ) => {
      state.selectedResourceCategories = action.payload;
    },
    setSelectedEmployeeRoles: (
      state,
      action: PayloadAction<EmployeeRole[]>,
    ) => {
      state.selectedEmployeeRoles = action.payload;
    },
    resetState: (state) => {
      Object.assign(state, initialState);
    },
    clearFilters: (state) => {
      state.searchInput = "";
      state.showOnlyFavorites = false;
      state.selectedResourceType = null;
      state.selectedTag = null;
      state.selectedResourceTypes = [];
      state.selectedTrades = [];
      state.selectedResourceCategories = [];
      state.selectedEmployeeRoles = [];
    },
    hydrateFromCache: (
      state,
      action: PayloadAction<Partial<ResourceLibraryState>>,
    ) => {
      const cachedState = action.payload;

      if (cachedState.resources) {
        state.resources = cachedState.resources;
      }
      if (cachedState.page) {
        state.page = cachedState.page;
      }
      if (typeof cachedState.hasMore === "boolean") {
        state.hasMore = cachedState.hasMore;
      }
      if (cachedState.scrollPosition) {
        state.scrollPosition = cachedState.scrollPosition;
      }
      if (cachedState.lastFetchParams) {
        state.lastFetchParams = cachedState.lastFetchParams;
      }
      if (cachedState.searchInput !== undefined) {
        state.searchInput = cachedState.searchInput;
      }
      if (typeof cachedState.showOnlyFavorites === "boolean") {
        state.showOnlyFavorites = cachedState.showOnlyFavorites;
      }
      if (cachedState.selectedResourceType !== undefined) {
        state.selectedResourceType = cachedState.selectedResourceType;
      }
    },
  },
});

export const fetchResources =
  (params: GetResourceSearchResultsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      const response: GetResourceSearchResultsAPIResponse =
        await getResourceSearchResults(params);
      const data = response.data;

      dispatch(setResources(data.resources || []));
      dispatch(setPage(params.page || 1));
      dispatch(setLastFetchParams(params));

      const hasMore =
        !!data && data.resources.length >= (params.pageSize || 10);
      dispatch(setHasMore(hasMore));
    } catch (error) {
      console.error("Error fetching resources:", error);
      dispatch(setHasMore(false));
    }
  };

export const fetchMoreResources =
  (params: GetResourceSearchResultsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      const response: GetResourceSearchResultsAPIResponse =
        await getResourceSearchResults(params);
      const data = response.data;

      dispatch(appendResources(data.resources || []));
      dispatch(setPage(params.page || 1));
      dispatch(setLastFetchParams(params));

      const hasMore =
        !!data && data.resources.length >= (params.pageSize || 10);
      dispatch(setHasMore(hasMore));
    } catch (error) {
      console.error("Error loading more resources:", error);
      dispatch(setHasMore(false));
    }
  };

export const {
  setResources,
  appendResources,
  setPage,
  setHasMore,
  setSearchInput,
  setShowOnlyFavorites,
  setScrollPosition,
  setLastFetchParams,
  setSelectedResourceTypes,
  setSelectedTrades,
  setSelectedResourceCategories,
  setSelectedEmployeeRoles,
  setIsRestoringState,
  clearFilters,
  hydrateFromCache,
} = resourceLibrarySlice.actions;

export default resourceLibrarySlice.reducer;
