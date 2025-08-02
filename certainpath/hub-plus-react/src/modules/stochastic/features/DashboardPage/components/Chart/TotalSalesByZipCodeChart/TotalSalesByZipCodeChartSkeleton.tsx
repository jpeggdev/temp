import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";

export function TotalSalesByZipCodeChartSkeleton() {
  return (
    <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
      <CardHeader>
        <CardTitle>
          <div className="h-6 rounded bg-gray-300 dark:bg-gray-700 max-w-[250px] w-full sm:w-64" />
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="w-full h-[400px] rounded bg-gray-300 dark:bg-gray-700" />
      </CardContent>
    </Card>
  );
}
