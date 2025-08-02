import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import { Column } from "@/components/Datatable/types";
import { Button } from "@/components/ui/button";
import { Pencil, Trash2 } from "lucide-react";

export function createCampaignProductsColumns(
  onEdit: (product: CampaignProduct) => void,
  onDelete: (product: CampaignProduct) => void,
): Column<CampaignProduct>[] {
  return [
    {
      header: "Name",
      accessorKey: "name",
      cell: ({ row }) => (
        <div className="whitespace-normal">{row.original.name}</div>
      ),
    },
    {
      header: "Description",
      accessorKey: "description",
      cell: ({ row }) => (
        <div className="whitespace-normal">{row.original.description}</div>
      ),
    },
    {
      header: "Prospect Price",
      accessorKey: "prospectPrice",
      cell: ({ row }) => row.original.prospectPrice || "",
    },
    {
      header: "Customer Price",
      accessorKey: "customerPrice",
      cell: ({ row }) => row.original.customerPrice || "",
    },
    {
      header: "Status",
      accessorKey: "isActive",
      cell: ({ row }) => (
        <span
          className={
            row.original.isActive !== false
              ? "px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-gray-800"
              : "px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
          }
        >
          {row.original.isActive !== false ? "Active" : "Inactive"}
        </span>
      ),
    },
    {
      header: "Actions",
      id: "actions",
      cell: ({ row }) => (
        <div className="flex space-x-2">
          <Button
            onClick={() => onEdit(row.original)}
            size="icon"
            title="Edit"
            variant="ghost"
          >
            <Pencil className="h-4 w-4" />
          </Button>
          <Button
            onClick={() => onDelete(row.original)}
            size="icon"
            title="Delete"
            variant="ghost"
          >
            <Trash2 className="h-4 w-4" />
          </Button>
        </div>
      ),
    },
  ];
}
