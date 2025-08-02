export interface DataTablePaginationProps {
  totalCount: number;
  totalPages: number;
  pageIndex: number;
  pageSize: number;
  onPageChange: (pageIndex: number, pageSize: number) => void;
}

export interface PaginationState {
  pageIndex: number;
  pageSize: number;
}
