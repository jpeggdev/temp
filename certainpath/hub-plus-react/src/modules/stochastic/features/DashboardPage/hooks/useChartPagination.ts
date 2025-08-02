import { useState, useMemo } from "react";

export function useChartPagination<T>(items: T[], itemsPerPage = 10) {
  const [page, setPage] = useState(0);

  const totalPages = useMemo(() => {
    return Math.ceil(items.length / itemsPerPage);
  }, [items.length, itemsPerPage]);

  const currentPageItems = useMemo(() => {
    const start = page * itemsPerPage;
    return items.slice(start, start + itemsPerPage);
  }, [items, page, itemsPerPage]);

  useMemo(() => {
    if (page >= totalPages && totalPages > 0) {
      setPage(totalPages - 1);
    }
  }, [page, totalPages]);

  return {
    page,
    setPage,
    totalPages,
    currentPageItems,
  };
}
