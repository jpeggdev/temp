import { Column } from "@/components/Datatable/types";
import { Voucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/createVoucher/types";
import { Badge } from "@/components/Badge/Badge";
import React from "react";
import VoucherActionMenu from "../VoucherActionMenu/VoucherActionMenu";

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

interface voucherColumnProps {
  handleEditVoucher: (id: number) => void;
  handleShowDeleteModal: (id: number) => void;
}

export function voucherColumns({
  handleEditVoucher,
  handleShowDeleteModal,
}: voucherColumnProps): Column<Voucher>[] {
  return [
    {
      header: "Name",
      enableSorting: true,
      accessorKey: "name",
    },
    {
      header: "Description",
      accessorKey: "description",
      cell: ({ row }) => {
        return row.original.description || "N/A";
      },
    },
    {
      header: "Company",
      accessorKey: "companyIdentifier",
      enableSorting: true,
      cell: ({ row }) => {
        return row.original.companyIdentifier;
      },
    },
    {
      header: "Available Seats",
      accessorKey: "availableSeats",
      cell: ({ row }) => {
        const availableSeats = row.original.availableSeats || 0;

        return (
          <div className="max-w-[250px] flex gap-2 flex-wrap items-start">
            <Badge className="whitespace-nowrap text-black" variant="outline">
              {availableSeats} seats
            </Badge>
          </div>
        );
      },
    },
    {
      header: "Usage",
      accessorKey: "usage",
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
        const voucher = row.original;
        return voucher.isActive ? (
          <VoucherActionMenu
            onDeleteVoucher={handleShowDeleteModal}
            onEditVoucher={handleEditVoucher}
            voucherId={voucher.id}
          />
        ) : null;
      },
    },
  ];
}
