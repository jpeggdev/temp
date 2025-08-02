import React from "react";
import { Column } from "@/components/Datatable/types";
import EventInstructorActionMenu from "@/modules/eventRegistration/features/EventInstructorManagement/components/EventInstructorActionMenu/EventInstructorActionMenu";

export interface EventInstructorItem {
  id: number;
  name: string;
  email: string;
  phone: string | null;
}

interface EventInstructorColumnsProps {
  onEditInstructor: (id: number) => void;
  onDeleteInstructor: (id: number) => void;
}

export function eventInstructorColumns({
  onEditInstructor,
  onDeleteInstructor,
}: EventInstructorColumnsProps): Column<EventInstructorItem>[] {
  return [
    {
      header: "ID",
      accessorKey: "id",
      enableSorting: true,
      cell: ({ row }) => row.original.id,
    },
    {
      header: "Name",
      accessorKey: "name",
      enableSorting: true,
      cell: ({ row }) => row.original.name || "--",
    },
    {
      header: "Email",
      accessorKey: "email",
      enableSorting: true,
      cell: ({ row }) => row.original.email || "--",
    },
    {
      header: "Phone",
      accessorKey: "phone",
      enableSorting: true,
      cell: ({ row }) => row.original.phone || "--",
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const instructorId = row.original.id;
        return (
          <EventInstructorActionMenu
            instructorId={instructorId}
            onDeleteInstructor={onDeleteInstructor}
            onEditInstructor={onEditInstructor}
          />
        );
      },
    },
  ];
}
