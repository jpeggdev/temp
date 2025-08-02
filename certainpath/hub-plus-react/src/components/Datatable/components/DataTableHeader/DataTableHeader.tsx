import React from "react";
import {
  ChevronUpIcon,
  ChevronDownIcon,
  ChevronUpDownIcon,
} from "@heroicons/react/24/outline";
import { SortingState } from "@/components/Datatable/types";
import { DataTableHeaderProps } from "@/components/Datatable/components/DataTableHeader/types";

function toggleSortForColumn(
  columnId: string,
  oldSorting?: SortingState,
): SortingState {
  const currentSort = oldSorting?.[0];
  if (!currentSort || currentSort.id !== columnId) {
    return [{ id: columnId, desc: false }];
  } else if (currentSort.desc === false) {
    return [{ id: columnId, desc: true }];
  } else {
    return [];
  }
}

function DataTableHeader<T>({
  columns,
  sorting,
  onSortingChange,
}: DataTableHeaderProps<T>) {
  return (
    <thead className="[&_tr]:border-b">
      <tr className="bg-gray-50/75">
        {columns.map((column) => {
          const rawHeaderKey = column.id ?? column.accessorKey;
          const safeHeaderKey = String(rawHeaderKey);

          const justifyContentClass =
            column.id === "actions" ? "justify-end" : "justify-start";

          let rawContent: unknown;
          if (typeof column.header === "function") {
            rawContent = column.header();
          } else if (column.header !== undefined) {
            rawContent = column.header;
          } else {
            rawContent = column.accessorKey ?? "";
          }

          if (typeof rawContent === "symbol") {
            rawContent = rawContent.toString();
          }
          const content = rawContent as React.ReactNode;

          const currentSort = sorting?.[0];
          const isSorted = currentSort?.id === safeHeaderKey;
          const isDesc = isSorted && currentSort.desc;

          let sortIcon: React.ReactNode = null;
          if (column.enableSorting) {
            if (!isSorted) {
              sortIcon = (
                <ChevronUpDownIcon className="h-4 w-4 text-gray-400" />
              );
            } else {
              sortIcon = isDesc ? (
                <ChevronDownIcon className="h-4 w-4 text-gray-500" />
              ) : (
                <ChevronUpIcon className="h-4 w-4 text-gray-500" />
              );
            }
          }

          const handleClick = column.enableSorting
            ? () => {
                if (!onSortingChange) return;
                onSortingChange((old) =>
                  toggleSortForColumn(safeHeaderKey, old),
                );
              }
            : undefined;

          return (
            <th
              className={`h-11 px-4 align-middle font-semibold text-muted-foreground ${
                column.enableSorting ? "cursor-pointer select-none" : ""
              }`}
              key={safeHeaderKey}
              onClick={handleClick}
            >
              <div className={`flex items-center gap-1 ${justifyContentClass}`}>
                {content}
                {sortIcon}
              </div>
            </th>
          );
        })}
      </tr>
    </thead>
  );
}

export default DataTableHeader;
