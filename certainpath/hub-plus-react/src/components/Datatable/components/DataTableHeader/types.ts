import { Column, OnChangeFn, SortingState } from "@/components/Datatable/types";

export interface DataTableHeaderProps<T> {
  columns: Column<T>[];
  sorting?: SortingState;
  onSortingChange?: OnChangeFn<SortingState>;
}
