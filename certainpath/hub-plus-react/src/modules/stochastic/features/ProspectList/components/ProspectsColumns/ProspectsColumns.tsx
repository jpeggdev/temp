import { StochasticProspect } from "@/api/fetchStochasticProspects/types";
import { Column } from "@/components/Datatable/types";
import { Switch } from "@/components/ui/switch";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";

interface CreateProspectsColumnsProps {
  onToggleDoNotMail: (prospectId: number, newValue: boolean) => void;
}

export function createStochasticProspectsColumns({
  onToggleDoNotMail,
}: CreateProspectsColumnsProps): Column<StochasticProspect>[] {
  return [
    {
      header: "ID",
      enableSorting: true,
      accessorKey: "id",
    },
    {
      header: "Full Name",
      accessorKey: "fullName",
      enableSorting: true,
    },
    {
      header: "Address 1",
      cell: ({ row }) => row.original.address?.address1 ?? "N/A",
    },
    {
      header: "City",
      cell: ({ row }) => row.original.address?.city ?? "N/A",
    },
    {
      header: "State",
      cell: ({ row }) => row.original.address?.stateCode ?? "N/A",
    },
    {
      header: "Postal Code",
      cell: ({ row }) => {
        const postalCode = row.original.address?.postalCode;
        if (!postalCode) {
          return "N/A";
        }
        return postalCode.length <= 5
          ? postalCode
          : postalCode.slice(0, 5) + "-" + postalCode.slice(5);
      },
    },
    {
      header: "Preferred",
      cell: ({ row }) => (row.original.isPreferred ? "Yes" : "No"),
    },
    {
      header: "Do Not Mail",
      id: "doNotMail",
      cell: ({ row }) => {
        const isDoNotMail = row.original.doNotMail ?? false;
        const isGlobalDoNotMail =
          row.original.address?.isGlobalDoNotMail ?? false;
        const prospectId = row.original.id;

        const handleChange = (checked: boolean) => {
          if (!isGlobalDoNotMail) {
            onToggleDoNotMail(prospectId, checked);
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
    {
      header: "Do Not Contact",
      cell: ({ row }) => (row.original.doNotContact ? "Yes" : "No"),
    },
    {
      header: "Created At",
      enableSorting: true,
      accessorKey: "createdAt",
      cell: ({ row }) =>
        row.original.createdAt
          ? new Date(row.original.createdAt).toLocaleDateString()
          : "N/A",
    },
    {
      header: "Updated At",
      enableSorting: true,
      accessorKey: "updatedAt",
      cell: ({ row }) =>
        row.original.updatedAt
          ? new Date(row.original.updatedAt).toLocaleDateString()
          : "N/A",
    },
  ];
}
