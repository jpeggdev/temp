import React from "react";

export interface ColumnSort {
  id: string;
  desc: boolean;
}

export type SortingState = ColumnSort[];

export type OnChangeFn<T> = (updaterOrValue: T | ((old: T) => T)) => void;

export interface Column<T> {
  id?: string;
  accessorKey?: keyof T | string;
  header?: string | (() => React.ReactNode);
  cell?: (props: { row: { original: T } }) => React.ReactNode;
  enableSorting?: boolean;
}

export interface DataTableProps<T> {
  columns: Column<T>[];
  data: T[];
  totalCount: number;
  pageIndex: number;
  pageSize: number;
  onPageChange: (pageIndex: number, pageSize: number) => void;
  sorting?: SortingState;
  onSortingChange?: OnChangeFn<SortingState>;
  loading?: boolean;
  error?: string | null;
  rowKeyExtractor?: (item: T, index: number) => string | number;
  noDataMessage?: string;
}
