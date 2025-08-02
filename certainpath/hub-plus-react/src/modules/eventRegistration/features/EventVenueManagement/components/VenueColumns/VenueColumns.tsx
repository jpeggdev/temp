import { Column } from "@/components/Datatable/types";
import { Venue } from "@/modules/eventRegistration/features/EventVenueManagement/api/createVenue/types";
import React from "react";
import VenueActionMenu from "@/modules/eventRegistration/features/EventVenueManagement/components/VenueActionMenu/VenueActionMenu";

interface venueColumnProps {
  handleEditVenue: (id: number) => void;
  handleShowDeleteModal: (id: number) => void;
}

export function venueColumns({
  handleEditVenue,
  handleShowDeleteModal,
}: venueColumnProps): Column<Venue>[] {
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
      header: "Address",
      accessorKey: "address",
      enableSorting: true,
      cell: ({ row }) => {
        return row.original.address;
      },
    },
    {
      header: "Address 2",
      accessorKey: "address2",
      enableSorting: true,
      cell: ({ row }) => {
        return row.original.address2 || "N/A";
      },
    },
    {
      header: "City",
      accessorKey: "city",
      enableSorting: true,
      cell: ({ row }) => {
        return row.original.city;
      },
    },
    {
      header: "State",
      accessorKey: "state",
      enableSorting: true,
      cell: ({ row }) => {
        return row.original.state;
      },
    },
    {
      header: "Postal Code",
      accessorKey: "postalCode",
      enableSorting: true,
      cell: ({ row }) => {
        return row.original.postalCode;
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const venue = row.original;
        return venue.isActive ? (
          <VenueActionMenu
            onDeleteVenue={handleShowDeleteModal}
            onEditVenue={handleEditVenue}
            venueId={venue.id}
          />
        ) : null;
      },
    },
  ];
}
