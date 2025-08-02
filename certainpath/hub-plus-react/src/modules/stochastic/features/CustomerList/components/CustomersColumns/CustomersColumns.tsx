import { StochasticCustomer } from "@/api/fetchStochasticCustomers/types";
import { formatCurrency } from "@/utils/formatCurrency";
import { Column } from "@/components/Datatable/types";
import React from "react";
import { Switch } from "@/components/ui/switch";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";

interface CreateCustomersColumnsProps {
  onToggleDoNotMail: (customerId: number, newValue: boolean) => void;
}

export function createCustomersColumns({
  onToggleDoNotMail,
}: CreateCustomersColumnsProps): Column<StochasticCustomer>[] {
  return [
    {
      header: "ID",
      accessorKey: "id",
      enableSorting: true,
    },
    {
      header: "Name",
      accessorKey: "name",
      enableSorting: true,
    },
    {
      header: "Has Installation",
      accessorKey: "hasInstallation",
      enableSorting: true,
      cell: ({ row }) => (row.original.hasInstallation ? "Yes" : "No"),
    },
    {
      header: "Has Subscription",
      accessorKey: "hasSubscription",
      enableSorting: true,
      cell: ({ row }) => (row.original.hasSubscription ? "Yes" : "No"),
    },
    {
      header: "Postal Code",
      // Using 'id' here, or you could omit it entirely.
      id: "postalCode",
      cell: ({ row }) => {
        const postalCode = row.original.address?.postalCode;
        if (!postalCode) {
          return "N/A";
        }
        if (postalCode.length <= 5) {
          return postalCode;
        }
        return postalCode.slice(0, 5) + "-" + postalCode.slice(5);
      },
    },
    {
      header: "Count Invoices",
      accessorKey: "countInvoices",
      enableSorting: true,
    },
    {
      header: "Invoice Total",
      accessorKey: "invoiceTotal",
      enableSorting: true,
      cell: ({ row }) => formatCurrency(row.original.invoiceTotal),
    },
    {
      header: "Lifetime Value",
      accessorKey: "lifetimeValue",
      enableSorting: true,
      cell: ({ row }) => formatCurrency(row.original.lifetimeValue),
    },
    {
      header: "First Invoiced At",
      accessorKey: "firstInvoicedAt",
      enableSorting: true,
      cell: ({ row }) =>
        row.original.firstInvoicedAt
          ? new Date(row.original.firstInvoicedAt).toLocaleDateString()
          : "N/A",
    },
    {
      header: "Last Invoiced At",
      accessorKey: "lastInvoicedAt",
      enableSorting: true,
      cell: ({ row }) =>
        row.original.lastInvoicedAt
          ? new Date(row.original.lastInvoicedAt).toLocaleDateString()
          : "N/A",
    },
    {
      header: "Do Not Mail",
      id: "doNotMail",
      cell: ({ row }) => {
        const isDoNotMail = row.original.doNotMail ?? false;
        const isGlobalDoNotMail =
          row.original.address?.isGlobalDoNotMail ?? false;
        const customerId = row.original.id;

        const handleChange = (checked: boolean) => {
          if (!isGlobalDoNotMail) {
            onToggleDoNotMail(customerId, checked);
          }
        };

        const stopPropagation = (e: React.MouseEvent) => {
          e.stopPropagation();
        };

        const switchComponent = (
          <Switch
            checked={isDoNotMail || isGlobalDoNotMail}
            className={isGlobalDoNotMail ? "cursor-not-allowed" : ""}
            disabled={isGlobalDoNotMail}
            onCheckedChange={handleChange}
            onClick={stopPropagation}
          />
        );

        if (isGlobalDoNotMail) {
          return (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <div className="cursor-not-allowed">{switchComponent}</div>
                </TooltipTrigger>
                <TooltipContent className="text-white">
                  <p>Globally disabled - cannot be changed</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          );
        }

        return switchComponent;
      },
    },
  ];
}
