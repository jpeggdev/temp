import React from "react";

interface MetricCardProps {
  label: string;
  value: string | number;
}

export const ChartMetricCard = ({ label, value }: MetricCardProps) => (
  <div className="bg-gray-50 dark:bg-gray-800 rounded-md p-4 shadow-sm text-center">
    <div className="text-gray-500 text-sm">{label}</div>
    <div className="font-bold text-lg">{value}</div>
  </div>
);
