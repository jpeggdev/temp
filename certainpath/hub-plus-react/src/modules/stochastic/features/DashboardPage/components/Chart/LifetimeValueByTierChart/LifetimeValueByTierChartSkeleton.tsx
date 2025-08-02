import React from "react";
import { Card, CardContent, CardHeader } from "@/components/Card/Card";

export default function LifetimeValueByTierChartSkeleton() {
  return (
    <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
      <CardHeader className="flex flex-row items-center justify-between">
        <div className="flex flex-col gap-2 w-72">
          <div className="h-8 bg-gray-300 dark:bg-gray-700 rounded w-48" />
          <div className="h-4 bg-gray-300 dark:bg-gray-700 rounded w-60" />
        </div>
        <div className="h-6 w-48 bg-gray-300 dark:bg-gray-700 rounded" />
      </CardHeader>
      <CardContent>
        <div className="h-[500px] w-full bg-gray-300 dark:bg-gray-700 rounded" />
      </CardContent>
    </Card>
  );
}
