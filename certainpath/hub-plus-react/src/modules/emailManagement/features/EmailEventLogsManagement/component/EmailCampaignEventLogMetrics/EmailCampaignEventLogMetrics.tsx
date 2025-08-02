import React from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { EmailCampaignEventLogsMetadata } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogMetadata/types";

interface EmailCampaignEventLogMetricsProps {
  emailCampaignEventLogsMetadata: EmailCampaignEventLogsMetadata;
}

const EmailCampaignEventLogMetrics: React.FC<
  EmailCampaignEventLogMetricsProps
> = ({ emailCampaignEventLogsMetadata }) => {
  return (
    <>
      <Card className="flex-1 min-w-[200px]">
        <CardHeader>
          <CardTitle className="text-base border-b pb-2">Delivered</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="text-3xl font-bold text-gray-900">
            {emailCampaignEventLogsMetadata.emailEventCount.delivered}
          </div>
          <p className="text-sm text-gray-500">
            {emailCampaignEventLogsMetadata.emailEventRate.delivered}%
            deliverability rate
          </p>
        </CardContent>
      </Card>

      <Card className="flex-1 min-w-[200px]">
        <CardHeader>
          <CardTitle className="text-base border-b pb-2">Opened</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="text-3xl font-bold text-gray-900">
            {emailCampaignEventLogsMetadata.emailEventCount.opened}
          </div>
          <p className="text-sm text-gray-500">
            {emailCampaignEventLogsMetadata.emailEventRate.opened}% open rate
          </p>
        </CardContent>
      </Card>

      <Card className="flex-1 min-w-[200px]">
        <CardHeader>
          <CardTitle className="text-base border-b pb-2">Clicked</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="text-3xl font-bold text-gray-900">
            {emailCampaignEventLogsMetadata.emailEventCount.clicked}
          </div>
          <p className="text-sm text-gray-500">
            {emailCampaignEventLogsMetadata.emailEventRate.clicked}% click rate
          </p>
        </CardContent>
      </Card>

      <Card className="flex-1 min-w-[200px]">
        <CardHeader>
          <CardTitle className="text-base border-b pb-2">
            Bounced/Rejected
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="text-3xl font-bold text-gray-900">
            {emailCampaignEventLogsMetadata.emailEventCount.failed}
          </div>
          <p className="text-sm text-gray-500">
            {emailCampaignEventLogsMetadata.emailEventRate.failed}% failure rate
          </p>
        </CardContent>
      </Card>
    </>
  );
};

export default EmailCampaignEventLogMetrics;
