import React from "react";
import DataTableHeader from "@/components/Datatable/components/DataTableHeader/DataTableHeader";
import DataTablePagination from "@/components/Datatable/components/DataTablePagination/DataTablePagination";
import LoadingIndicator from "@/components/LoadingIndicator/LoadingIndicator";
import { DataTableProps } from "@/components/Datatable/types";

const DataTable = <T extends object>({
  columns,
  data,
  totalCount,
  pageIndex,
  pageSize,
  onPageChange,
  sorting,
  onSortingChange,
  loading = false,
  error,
  rowKeyExtractor,
  noDataMessage = "No records found",
}: DataTableProps<T>) => {
  const totalPages = Math.ceil(totalCount / pageSize);

  return (
    <div className="relative space-y-4">
      {loading && (
        <div className="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50">
          <LoadingIndicator isFullScreen={false} />
        </div>
      )}

      {error && <div className="text-red-600 text-sm">Error: {error}</div>}

      <div className="rounded-md border shadow-sm">
        <div className="relative w-full overflow-auto">
          <table className="w-full caption-bottom text-sm">
            <DataTableHeader
              columns={columns}
              onSortingChange={onSortingChange}
              sorting={sorting}
            />

            <tbody className="[&_tr:last-child]:border-0">
              {data.length === 0 ? (
                <tr className="border-b transition-colors hover:bg-muted/50">
                  <td
                    className="p-10 text-center text-muted-foreground"
                    colSpan={columns.length}
                  >
                    {noDataMessage}
                  </td>
                </tr>
              ) : (
                data.map((item, index) => {
                  const rowKey = rowKeyExtractor
                    ? rowKeyExtractor(item, index)
                    : index;

                  return (
                    <tr
                      className="border-b transition-colors hover:bg-muted/50"
                      key={rowKey}
                    >
                      {columns.map((column) => {
                        const rawCellKey = column.id || column.accessorKey;
                        const cellKey = String(rawCellKey);

                        const alignmentClass =
                          column.id === "actions" ? "text-right" : "text-left";

                        if (column.cell) {
                          return (
                            <td
                              className={`p-4 align-middle [&:has([role=checkbox])]:pr-0 ${alignmentClass}`}
                              key={cellKey}
                            >
                              {column.cell({ row: { original: item } })}
                            </td>
                          );
                        }

                        if (column.accessorKey) {
                          const typedKey = column.accessorKey as keyof T;
                          const cellValue = item[typedKey];
                          const displayValue =
                            cellValue != null ? String(cellValue) : "";

                          return (
                            <td
                              className={`p-4 align-middle [&:has([role=checkbox])]:pr-0 ${alignmentClass}`}
                              key={cellKey}
                            >
                              {displayValue}
                            </td>
                          );
                        }

                        return (
                          <td
                            className={`p-4 align-middle ${alignmentClass}`}
                            key={cellKey}
                          />
                        );
                      })}
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      <DataTablePagination
        onPageChange={onPageChange}
        pageIndex={pageIndex}
        pageSize={pageSize}
        totalCount={totalCount}
        totalPages={totalPages}
      />
    </div>
  );
};

export default DataTable;
