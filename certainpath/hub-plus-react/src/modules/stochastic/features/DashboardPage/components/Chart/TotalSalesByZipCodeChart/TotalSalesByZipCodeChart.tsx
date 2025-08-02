import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";
import {
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  BarChart,
  ReferenceLine,
} from "recharts";
import { useTheme } from "@/context/ThemeContext";
import { formatCurrency } from "@/modules/stochastic/features/DashboardPage/utils/chart";
import { TotalSalesByZipCodeDataItem } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";

type TotalSalesByZipCodeChartProps = {
  chartId?: string;
  initialData: TotalSalesByZipCodeDataItem[];
};

export default function TotalSalesByZipCodeChart({
  initialData,
}: TotalSalesByZipCodeChartProps) {
  const { theme } = useTheme();

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Total Sales By Zip Code
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ResponsiveContainer height={400} width="100%">
          <BarChart
            data={initialData}
            margin={{ top: 20, right: 40, left: 45, bottom: 5 }}
          >
            <CartesianGrid
              stroke={theme === "dark" ? "#374151" : "#e5e7eb"}
              strokeDasharray="3 3"
            />
            <XAxis
              dataKey="postalCode"
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
            />
            <YAxis
              domain={["auto", "auto"]}
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              tickFormatter={formatCurrency}
            />
            <Tooltip
              contentStyle={{
                backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
                borderColor: theme === "dark" ? "#374151" : "#E5E7EB",
                color: theme === "dark" ? "#F3F4F6" : "#1F2937",
              }}
              formatter={(value) => formatCurrency(value as number)}
              itemStyle={{ color: theme === "dark" ? "#F3F4F6" : "#1F2937" }}
              labelFormatter={(label) => `ZIP Code: ${label}`}
              labelStyle={{ color: theme === "dark" ? "#F3F4F6" : "#1F2937" }}
            />
            <ReferenceLine stroke="#ccc" y={0} />
            <Bar
              dataKey="totalSales"
              fill={theme === "dark" ? "#3b82f6" : "#8884d8"}
              name="Total Sales"
            />
          </BarChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
}
