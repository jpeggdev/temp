import { Column } from "@/components/Datatable/types";
import { Location } from "@/modules/stochastic/features/LocationsList/api/createLocation/types";
import { Badge } from "@/components/Badge/Badge";
import React from "react";
import LocationListActionMenu from "@/modules/stochastic/features/LocationsList/components/LocationListActionMenu/LocationListActionMenu";

interface LocationListColumnsProps {
  onEditLocation: (id: number) => void;
  onDeleteLocation: (id: number) => void;
}

export function LocationListColumns({
  onEditLocation,
  onDeleteLocation,
}: LocationListColumnsProps): Column<Location>[] {
  return [
    {
      header: "Name",
      accessorKey: "name",
      enableSorting: true,
    },
    {
      header: "Description",
      accessorKey: "description",
      cell: ({ row }) => {
        const description = row.original?.description?.trim();
        return description ? description : "N/A";
      },
    },
    {
      header: "Postal Codes",
      accessorKey: "postalCodes",
      cell: ({ row }) => {
        const postalCodes = row.original.postalCodes;

        if (Array.isArray(postalCodes) && postalCodes.length > 0) {
          return (
            <div className="max-w-[250px] flex gap-2 flex-wrap items-start">
              {postalCodes.map((postalCode, index) => (
                <Badge
                  className="whitespace-nowrap text-white"
                  key={index}
                  variant="default"
                >
                  {postalCode}
                </Badge>
              ))}
            </div>
          );
        } else {
          return "N/A";
        }
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const { id: locationId, isActive } = row.original;
        return isActive ? (
          <LocationListActionMenu
            locationId={locationId}
            onDeleteLocation={onDeleteLocation}
            onEditLocation={onEditLocation}
          />
        ) : null;
      },
    },
  ];
}
