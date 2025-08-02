import { Campaign } from "@/api/fetchCompanyCampaigns/types";
import { Column } from "@/components/Datatable/types";

export function createBillingCampaignsColumns(): Column<Campaign>[] {
  return [
    {
      header: "Campaign Name",
      accessorKey: "name",
      cell: ({ row }) => {
        return (
          <>
            <strong>{row.original.name || "N/A"}</strong>
            {row.original.campaignProduct && (
              <>
                <br />
                {row.original.campaignProduct.name}
              </>
            )}
          </>
        );
      },
    },
    {
      header: "End Date",
      accessorKey: "endDate",
      cell: ({ row }) => row.original.endDate || "N/A",
    },
    {
      header: "Quantity",
      accessorKey: "quantity",
      cell: ({ row }) =>
        (row.original.campaignPricing &&
          row.original.campaignPricing.actualQuantity) ||
        "N/A",
    },
    {
      header: "Material Cost",
      accessorKey: "materialCost",
      cell: ({ row }) =>
        (row.original.campaignPricing &&
          row.original.campaignPricing.materialExpense) ||
        "N/A",
    },
    {
      header: "Postage Cost",
      accessorKey: "postageCost",
      cell: ({ row }) =>
        (row.original.campaignPricing &&
          row.original.campaignPricing.postageExpense) ||
        "N/A",
    },
    {
      header: "Total",
      accessorKey: "total",
      cell: ({ row }) =>
        (row.original.campaignPricing &&
          row.original.campaignPricing.totalExpense) ||
        "N/A",
    },
    {
      id: "actions",
      header: "Actions",
      /*cell: ({ row }) => (
        <CampaignBillingActionMenu campaignId={row.original.id} />
      ),*/
    },
  ];
}
