import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";

export default function TotalSalesNewVsExistingCustomerChartSkeleton() {
  return (
    <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
      <CardHeader>
        <CardTitle className="h-6 max-w-full w-40 sm:w-64 bg-gray-300 dark:bg-gray-700 rounded" />
      </CardHeader>
      <CardContent>
        <div className="h-[500px] w-full bg-gray-300 dark:bg-gray-700 rounded" />
      </CardContent>
    </Card>
  );
}
