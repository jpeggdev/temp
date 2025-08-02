import React from "react";
import { Button } from "@/components/ui/button";
import { DataTablePaginationProps } from "@/components/Datatable/components/DataTablePagination/types";

const DataTablePagination: React.FC<DataTablePaginationProps> = ({
  totalCount,
  totalPages,
  pageIndex,
  pageSize,
  onPageChange,
}) => {
  return (
    <div className="flex flex-col gap-2 px-2 sm:flex-row sm:items-center sm:justify-between">
      <div className="text-sm text-muted-foreground">
        <span className="font-medium">{totalCount}</span> records total, page{" "}
        <span className="font-medium">{pageIndex + 1}</span> of{" "}
        <span className="font-medium">{totalPages}</span>
      </div>

      <div className="flex items-center space-x-6 lg:space-x-8">
        <div className="flex items-center space-x-2">
          <p className="text-sm font-medium">Items per page</p>
          <select
            className="h-9 w-[70px] rounded-md border border-input bg-transparent px-3 py-1 text-sm"
            onChange={(e) => onPageChange(0, Number(e.target.value))}
            value={pageSize}
          >
            {[10, 20, 30, 40, 50].map((size) => (
              <option key={size} value={size}>
                {size}
              </option>
            ))}
          </select>
        </div>

        <div className="flex items-center space-x-2">
          <Button
            className="h-9 px-4"
            disabled={pageIndex === 0}
            onClick={() => onPageChange(pageIndex - 1, pageSize)}
            size="sm"
            variant="outline"
          >
            Previous
          </Button>
          <Button
            className="h-9 px-4"
            disabled={pageIndex >= totalPages - 1}
            onClick={() => onPageChange(pageIndex + 1, pageSize)}
            size="sm"
            variant="outline"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  );
};

export default DataTablePagination;
