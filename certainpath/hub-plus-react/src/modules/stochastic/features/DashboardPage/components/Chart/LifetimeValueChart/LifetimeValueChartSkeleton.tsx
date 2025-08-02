import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";

export default function LifetimeValueChartSkeleton() {
  return (
    <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
      <CardHeader>
        <CardTitle className="h-8 w-48 bg-gray-300 dark:bg-gray-700 rounded" />
      </CardHeader>
      <CardContent>
        <div className="h-[400px] w-full bg-gray-300 dark:bg-gray-700 rounded" />
      </CardContent>
    </Card>
  );
}
