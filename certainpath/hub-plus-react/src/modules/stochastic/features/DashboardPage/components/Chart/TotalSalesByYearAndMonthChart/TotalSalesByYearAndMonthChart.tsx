"use client";

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
  Legend,
  ResponsiveContainer,
  BarChart,
} from "recharts";
import { useTheme } from "@/context/ThemeContext";
import { TotalSalesByYearAndMonthChartDataItem } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesByYearAndMonthChartData/types";
import {
  formatCurrency,
  generateColors,
} from "@/modules/stochastic/features/DashboardPage/utils/chart";
import ChartTooltipWithColoredYears from "@/modules/stochastic/features/DashboardPage/components/ChartTooltipWithColoredYears/ChartTooltipWithColoredYears";
import { ChartBarLegendWithColoredYear } from "@/modules/stochastic/features/DashboardPage/components/ChartBarLegendWithColoredYear/ChartBarLegendWithColoredYear";

const extractYearKeys = (
  data: TotalSalesByYearAndMonthChartDataItem[],
): string[] => {
  if (!data || data.length === 0) return [];
  return Object.keys(data[0]).filter((key) => key !== "month");
};

type TotalSalesByYearAndMonthChartProps = {
  chartId?: string;
  initialData: TotalSalesByYearAndMonthChartDataItem[];
};

export default function TotalSalesByYearAndMonthChart({
  initialData,
}: TotalSalesByYearAndMonthChartProps) {
  const { theme } = useTheme();
  const yearKeys = extractYearKeys(initialData);
  const colors = generateColors(yearKeys, theme);

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Total Sales By Year & Month
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ResponsiveContainer height={500} width="100%">
          <BarChart
            data={initialData}
            margin={{ top: 20, right: 30, left: 45, bottom: 5 }}
          >
            <CartesianGrid
              stroke={theme === "dark" ? "#374151" : "#e5e7eb"}
              strokeDasharray="3 3"
            />
            <XAxis
              dataKey="month"
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
            />
            <YAxis
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              tickFormatter={formatCurrency}
            />
            <Tooltip
              content={
                <ChartTooltipWithColoredYears
                  colors={colors}
                  labelKey="month"
                  labelPrefix="Month:"
                  valueSuffix=""
                />
              }
            />
            <Legend content={<ChartBarLegendWithColoredYear />} />
            {yearKeys.map((year) => (
              <Bar dataKey={year} fill={colors[year]} key={year} name={year} />
            ))}
          </BarChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
}
