import { useEffect, useState, useCallback } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import { AppDispatch } from "@/app/store";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import {
  fetchResources,
  fetchMoreResources,
  setResources,
  setPage,
  setHasMore,
  setScrollPosition,
  setLastFetchParams,
  setSearchInput,
  setShowOnlyFavorites,
  setSelectedTrades,
  setSelectedResourceTypes,
  setSelectedResourceCategories,
  setSelectedEmployeeRoles,
  setIsRestoringState,
  clearFilters,
} from "@/modules/hub/features/ResourceLibrary/slices/resourceLibrarySlice";
import { getResourceLibraryMetadataAction } from "@/modules/hub/features/ResourceLibrary/slices/resourceLibraryMetadataSlice";
import {
  FilterSidebarFormData,
  FilterSidebarFormSchema,
} from "@/modules/hub/features/ResourceLibrary/hooks/FilterSidebarFormSchema";
import {
  Trade,
  EmployeeRole,
} from "@/modules/hub/features/ResourceLibrary/api/getResourceLibratyMetadata/types";
import { ResourceTypeFacet } from "@/api/getResourceSearchResults/types";
import { ResourceCategory } from "@/api/fetchCreateUpdateResourceMetadata/types";

export function useResourcesLibrary() {
  const dispatch = useDispatch<AppDispatch>();

  const {
    resources,
    page,
    hasMore,
    lastFetchParams,
    scrollPosition,
    searchInput,
    showOnlyFavorites,
    selectedTrades,
    selectedResourceTypes,
    selectedResourceCategories,
    selectedEmployeeRoles,
  } = useSelector((state: RootState) => state.resourceLibrary);

  const { metadata, loadingGet } = useSelector(
    (state: RootState) => state.resourceLibraryMetadata,
  );

  const form = useForm<FilterSidebarFormData>({
    resolver: zodResolver(FilterSidebarFormSchema),
    defaultValues: {
      searchTerm: searchInput || "",
      isFavoriteOnly: showOnlyFavorites || false,
      trades: selectedTrades || [],
      contentTypes: selectedResourceTypes || [],
      categories: selectedResourceCategories || [],
      employeeRoles: selectedEmployeeRoles || [],
    },
    mode: "onChange",
  });

  const debouncedSearchTerm = useDebouncedValue(
    form.watch("searchTerm") ?? "",
    500,
  );
  const [isInitialLoading, setIsInitialLoading] = useState(true);
  const [loading, setLoading] = useState(false);

  // Sync form to Redux
  useEffect(() => {
    const subscription = form.watch((values) => {
      dispatch(setSearchInput(values.searchTerm || ""));
      dispatch(setShowOnlyFavorites(values.isFavoriteOnly || false));
      dispatch(
        setSelectedTrades((values.trades ?? []).filter((t): t is Trade => !!t)),
      );
      dispatch(
        setSelectedResourceTypes(
          (values.contentTypes ?? []).filter(
            (t): t is ResourceTypeFacet => !!t,
          ),
        ),
      );
      dispatch(
        setSelectedResourceCategories(
          (values.categories ?? []).filter((c): c is ResourceCategory => !!c),
        ),
      );
      dispatch(
        setSelectedEmployeeRoles(
          (values.employeeRoles ?? []).filter((e): e is EmployeeRole => !!e),
        ),
      );
    });
    return () => subscription.unsubscribe();
  }, [form, dispatch]);

  const buildRequestData = useCallback(() => {
    const values = form.getValues();
    return {
      searchTerm: debouncedSearchTerm?.trim() || undefined,
      showFavorites: values.isFavoriteOnly || undefined,
      tradeIds: values.trades?.map((t) => t.id),
      resourceTypeIds: values.contentTypes?.map((t) => t.id),
      categoryIds: values.categories?.map((c) => c.id),
      employeeRoleIds: values.employeeRoles?.map((e) => e.id),
      page: 1,
      pageSize: 10,
    };
  }, [form, debouncedSearchTerm]);

  const isStateRestorable = useCallback(() => {
    if (!lastFetchParams) return false;

    const getFiltersWithoutPagination = (params: Record<string, unknown>) => {
      const paginationKeys = ["page", "pageSize"];
      return Object.fromEntries(
        Object.entries(params).filter(([key]) => !paginationKeys.includes(key)),
      );
    };

    const currentFilters = getFiltersWithoutPagination(buildRequestData());
    const previousFilters = getFiltersWithoutPagination(lastFetchParams);

    return JSON.stringify(currentFilters) === JSON.stringify(previousFilters);
  }, [lastFetchParams, buildRequestData]);

  useEffect(() => {
    dispatch(getResourceLibraryMetadataAction());
  }, [dispatch]);

  useEffect(() => {
    if (!isInitialLoading && scrollPosition > 0) {
      requestAnimationFrame(() => {
        window.scrollTo({ top: scrollPosition, behavior: "auto" });
      });
    }
  }, [isInitialLoading, scrollPosition]);

  useEffect(() => {
    const saveScroll = () => {
      dispatch(setScrollPosition(window.scrollY));
    };
    window.addEventListener("beforeunload", saveScroll);
    window.addEventListener("pagehide", saveScroll);
    return () => {
      saveScroll();
      window.removeEventListener("beforeunload", saveScroll);
      window.removeEventListener("pagehide", saveScroll);
    };
  }, [dispatch]);

  useEffect(() => {
    const load = async () => {
      if (resources.length > 0 && lastFetchParams && isStateRestorable()) {
        dispatch(setIsRestoringState(true));
        setIsInitialLoading(false);
        return;
      }

      const request = buildRequestData();
      setLoading(true);
      dispatch(setResources([]));
      dispatch(setPage(1));
      dispatch(setHasMore(true));

      await dispatch(fetchResources(request));

      dispatch(setLastFetchParams(request));
      setLoading(false);
      setIsInitialLoading(false);
    };
    load();
  }, []);

  useEffect(() => {
    if (isInitialLoading) return;
    const request = buildRequestData();
    setLoading(true);
    dispatch(setResources([]));
    dispatch(setPage(1));
    dispatch(setHasMore(true));
    dispatch(fetchResources(request));
    dispatch(setLastFetchParams(request));
    setLoading(false);
  }, [
    debouncedSearchTerm,
    form.watch("isFavoriteOnly"),
    form.watch("contentTypes"),
    form.watch("trades"),
    form.watch("categories"),
    form.watch("employeeRoles"),
  ]);

  const fetchMore = useCallback(() => {
    const nextPage = page + 1;
    const request = { ...buildRequestData(), page: nextPage };
    dispatch(setPage(nextPage));
    dispatch(fetchMoreResources(request));
  }, [page, dispatch, buildRequestData]);

  const handleClearFilters = () => {
    dispatch(clearFilters());

    form.reset({
      searchTerm: "",
      isFavoriteOnly: false,
      trades: [],
      contentTypes: [],
      categories: [],
      employeeRoles: [],
    });
  };

  return {
    form,
    page,
    resources,
    hasMore,
    loading,
    isInitialLoading,
    fetchMore,
    handleClearFilters,
    sidebarMetadata: metadata,
    isMetadataLoading: loadingGet,
  };
}
