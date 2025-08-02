import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";

export function PercentageOfNewCustomersByZipCodeChartSkeleton() {
  return (
    <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
      <CardHeader>
        <CardTitle className="h-8 w-64 bg-gray-300 dark:bg-gray-700 rounded" />
      </CardHeader>

      <CardContent>
        <div className="h-[500px] w-full bg-gray-300 dark:bg-gray-700 rounded" />

        <div className="mt-8 space-y-3">
          <div className="h-5 w-48 mx-auto bg-gray-300 dark:bg-gray-700 rounded" />

          <div className="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-6 mt-6">
            {[...Array(4)].map((_, i) => (
              <div
                className="h-12 bg-gray-300 dark:bg-gray-700 rounded-md"
                key={i}
              />
            ))}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
