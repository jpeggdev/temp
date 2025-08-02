import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";
import {
  ComposedChart,
  Bar,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from "recharts";
import { useTheme } from "@/context/ThemeContext";
import {
  formatCurrency,
  formatPercentage,
} from "@/modules/stochastic/features/DashboardPage/utils/chart";

type ChartDataItem = {
  days?: string;
  month?: string;
  salesPercentage?: number;
  totalSales?: number;
  [key: string]: string | number | undefined;
};

type LifetimeValueChartProps = {
  chartId?: string;
  initialData: ChartDataItem[];
};

export default function LifetimeValueChart({
  initialData,
}: LifetimeValueChartProps) {
  const { theme } = useTheme();

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Lifetime Value
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ResponsiveContainer height={400} width="100%">
          <ComposedChart
            data={initialData}
            margin={{
              top: 5,
              right: 30,
              left: 45,
              bottom: 5,
            }}
          >
            <CartesianGrid
              stroke={theme === "dark" ? "#374151" : "#e5e7eb"}
              strokeDasharray="3 3"
            />
            <XAxis
              dataKey="salesPeriod"
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
            />
            <YAxis
              domain={[0, "dataMax"]}
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              tickFormatter={formatCurrency}
              yAxisId="left"
            />
            <YAxis
              domain={[0, 100]}
              orientation="right"
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              tickFormatter={formatPercentage}
              yAxisId="right"
            />
            <Tooltip
              contentStyle={{
                backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
                borderColor: theme === "dark" ? "#374151" : "#E5E7EB",
                color: theme === "dark" ? "#F3F4F6" : "#1F2937",
              }}
              formatter={(value, name) => {
                if (name === "Total Sales")
                  return formatCurrency(value as number);
                if (name === "Sales %")
                  return formatPercentage(value as number);
                return value;
              }}
            />
            <Legend
              wrapperStyle={{
                color: theme === "dark" ? "#F3F4F6" : "#1F2937",
              }}
            />
            <Bar
              dataKey="totalSales"
              fill={theme === "dark" ? "#60A5FA" : "#2E8BC0"}
              name="Total Sales"
              yAxisId="left"
            />
            <Line
              dataKey="salesPercentage"
              dot={{ fill: theme === "dark" ? "#F97316" : "#FF7300", r: 4 }}
              name="Sales %"
              stroke={theme === "dark" ? "#F97316" : "#FF7300"}
              strokeWidth={2}
              type="monotone"
              yAxisId="right"
            />
          </ComposedChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
}
