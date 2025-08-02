import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";

export function CustomersAverageInvoiceComparisonChartSkeleton() {
  return (
    <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
      <CardHeader>
        <CardTitle>
          <div className="h-8 rounded bg-gray-300 dark:bg-gray-700 max-w-[320px] w-full" />
        </CardTitle>
        <div className="mt-1 h-4 rounded bg-gray-300 dark:bg-gray-700 max-w-[400px] w-full" />
      </CardHeader>

      <CardContent>
        <div className="flex flex-col gap-1 mb-6">
          <div className="h-6 rounded bg-gray-300 dark:bg-gray-700 max-w-[210px] w-full" />
          <div className="h-6 rounded bg-gray-300 dark:bg-gray-700 max-w-[250px] w-full" />
        </div>

        <div className="h-[450px] w-full rounded bg-gray-300 dark:bg-gray-700" />
      </CardContent>
    </Card>
  );
}
