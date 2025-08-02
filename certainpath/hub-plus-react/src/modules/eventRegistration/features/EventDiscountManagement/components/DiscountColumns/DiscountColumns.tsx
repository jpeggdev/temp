import { Column } from "@/components/Datatable/types";
import React from "react";
import { Discount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/createDiscount/types";
import DiscountActionMenu from "@/modules/eventRegistration/features/EventDiscountManagement/components/DiscountActionMenu/DiscountActionMenu";
import { Badge } from "@/components/Badge/Badge";

interface discountColumnsProps {
  handleEditDiscount: (id: number) => void;
}

function formatLocalDateTime(dateStr?: string | null): string {
  if (dateStr == null) return "N/A";

  const date = new Date(dateStr);
  if (isNaN(date.getTime())) return "--";

  return date.toLocaleString(undefined, {
    weekday: "short",
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

function formatAmountValue(
  value: string | null | undefined,
  typeName: string | null,
): string {
  if (!value || isNaN(Number(value))) return "N/A";

  const numericValue = Number(value);

  if (typeName?.toLowerCase() === "percentage") {
    return `${numericValue}%`;
  }

  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(numericValue);
}

export function discountColumns({
  handleEditDiscount,
}: discountColumnsProps): Column<Discount>[] {
  return [
    {
      header: "Code",
      enableSorting: true,
      accessorKey: "code",
    },
    {
      header: "Description",
      accessorKey: "description",
      cell: ({ row }) => {
        return row.original.description || "N/A";
      },
    },
    {
      header: "Discount",
      accessorKey: "discountValue",
      enableSorting: true,
      cell: ({ row }) => {
        const { discountValue, discountType } = row.original;
        return formatAmountValue(discountValue, discountType?.name || null);
      },
    },
    {
      header: "Usage",
      accessorKey: "usage",
      enableSorting: true,
      cell: ({ row }) => {
        return (
          <div className="max-w-[250px] flex gap-2 flex-wrap items-start">
            <Badge
              className="whitespace-nowrap text-white bg-red-700 border-0"
              variant="outline"
            >
              {row.original.usage || "N/A"}
            </Badge>
          </div>
        );
      },
    },
    {
      header: "Minimum Purchase Amount",
      accessorKey: "minimumPurchaseAmount",
      enableSorting: true,
      cell: ({ row }) => {
        const { minimumPurchaseAmount } = row.original;
        return formatAmountValue(minimumPurchaseAmount, null);
      },
    },
    {
      header: "Start Date",
      accessorKey: "startDate",
      cell: ({ row }) => {
        const start = row.original.startDate;
        return formatLocalDateTime(start);
      },
    },
    {
      header: "End Date",
      accessorKey: "endDate",
      cell: ({ row }) => {
        const end = row.original.endDate;
        return formatLocalDateTime(end);
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const discount = row.original;
        return discount.isActive ? (
          <DiscountActionMenu
            discountId={discount.id}
            onEditDiscount={handleEditDiscount}
          />
        ) : null;
      },
    },
  ];
}
