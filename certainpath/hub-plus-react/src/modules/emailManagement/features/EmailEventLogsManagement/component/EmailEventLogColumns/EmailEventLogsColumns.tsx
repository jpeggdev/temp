import { Column } from "@/components/Datatable/types";
import { EmailCampaignEventLog } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogs/types";
import { Check, AlertTriangle } from "lucide-react";
import { formatDate } from "@/utils/dateUtils";

export function emailEventLogsColumns(): Column<EmailCampaignEventLog>[] {
  return [
    {
      header: "Time",
      accessorKey: "eventSentAt",
      enableSorting: true,
      cell: ({ row }) => <div>{formatDate(row.original.eventSentAt)}</div>,
    },
    {
      header: "Email",
      accessorKey: "email",
      enableSorting: true,
    },
    {
      header: "Subject",
      accessorKey: "subject",
      enableSorting: true,
    },
    {
      header: "Sent",
      accessorKey: "isSent",
      cell: ({ row }) =>
        row.original.isSent ? (
          <Check className="w-4 h-4 text-green-600" />
        ) : (
          <div className="w-2.5 h-2.5 rounded-full bg-gray-400 opacity-40" />
        ),
    },
    {
      header: "Delivered",
      accessorKey: "isDelivered",
      cell: ({ row }) =>
        row.original.isDelivered ? (
          <Check className="w-4 h-4 text-green-600" />
        ) : (
          <div className="w-2.5 h-2.5 rounded-full bg-gray-400 opacity-40" />
        ),
    },
    {
      header: "Opened",
      accessorKey: "isOpened",
      cell: ({ row }) =>
        row.original.isOpened ? (
          <Check className="w-4 h-4 text-green-600" />
        ) : (
          <div className="w-2.5 h-2.5 rounded-full bg-gray-400 opacity-40" />
        ),
    },
    {
      header: "Clicked",
      accessorKey: "isClicked",
      cell: ({ row }) =>
        row.original.isClicked ? (
          <Check className="w-4 h-4 text-green-600" />
        ) : (
          <div className="w-2.5 h-2.5 rounded-full bg-gray-400 opacity-40" />
        ),
    },
    {
      header: "Failed",
      accessorKey: "isBounced",
      cell: ({ row }) =>
        row.original.isBounced ? (
          <AlertTriangle className="w-4 h-4 text-red-600" />
        ) : (
          <div className="w-2.5 h-2.5 rounded-full bg-gray-400 opacity-40" />
        ),
    },
    {
      header: "Message ID",
      accessorKey: "messageId",
      enableSorting: true,
    },
  ];
}
