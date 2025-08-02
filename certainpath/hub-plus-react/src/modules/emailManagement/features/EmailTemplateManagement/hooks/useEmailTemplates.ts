import { useState, useEffect, useMemo, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  SortingState,
  PaginationState,
  OnChangeFn,
} from "@tanstack/react-table";
import { RootState } from "@/app/rootReducer";
import { FetchEmailTemplatesRequest } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplates/types";
import { useNavigate } from "react-router-dom";
import { useNotification } from "@/context/NotificationContext";
import { fetchEmailTemplatesAction } from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateListSlice";
import {
  deleteEmailTemplateAction,
  duplicateEmailTemplateAction,
} from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateSlice";

export function useEmailTemplates() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { emailTemplates, totalCount, loading, error } = useSelector(
    (state: RootState) => state.emailTemplateList,
  );
  const { showNotification } = useNotification();

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);

  const [filters, setFilters] = useState<{
    searchTerm?: string;
    isActive: number;
  }>({
    searchTerm: "",
    isActive: 1,
  });

  const requestData = useMemo<FetchEmailTemplatesRequest>(() => {
    return {
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : "ASC",
      searchTerm: filters.searchTerm,
      isActive: filters.isActive,
      pageSize: pagination.pageSize,
    };
  }, [pagination, sorting]);

  useEffect(() => {
    dispatch(fetchEmailTemplatesAction(requestData));
  }, [dispatch, requestData]);

  const handlePaginationChange: OnChangeFn<PaginationState> = useCallback(
    (updaterOrValue) => {
      setPagination(updaterOrValue);
    },
    [],
  );

  const handleSortingChange: OnChangeFn<SortingState> = useCallback(
    (updaterOrValue) => {
      setSorting(updaterOrValue);
    },
    [],
  );

  const handleEditEmailTemplate = useCallback(
    (id: number) => {
      navigate(`/email-management/email-templates/${id}/edit`);
    },
    [navigate],
  );

  const handleDuplicateEmailTemplate = useCallback(
    async (id: number) => {
      await dispatch(duplicateEmailTemplateAction(id));

      showNotification(
        "Successfully duplicated email template!",
        "Your email template has been successfully duplicated",
        "success",
      );

      dispatch(fetchEmailTemplatesAction(requestData));
    },
    [dispatch, requestData],
  );

  const handleDeleteEmailTemplate = useCallback(
    async (id: number) => {
      await dispatch(deleteEmailTemplateAction(id));

      showNotification(
        "Successfully deleted email template!",
        "Your email template has been successfully deleted",
        "success",
      );

      dispatch(fetchEmailTemplatesAction(requestData));
    },
    [dispatch, requestData],
  );

  const handleFilterChange = useCallback(
    (searchTerm: string, isActive: number) => {
      setFilters({ searchTerm, isActive });
      handlePaginationChange({
        pageIndex: 0,
        pageSize: pagination.pageSize,
      });
    },
    [handlePaginationChange, pagination.pageSize],
  );

  return {
    emailTemplates,
    totalCount,
    loading,
    error,
    filters,
    pagination,
    handleFilterChange,
    handleSortingChange,
    handlePaginationChange,
    handleEditEmailTemplate,
    handleDuplicateEmailTemplate,
    handleDeleteEmailTemplate,
  };
}
