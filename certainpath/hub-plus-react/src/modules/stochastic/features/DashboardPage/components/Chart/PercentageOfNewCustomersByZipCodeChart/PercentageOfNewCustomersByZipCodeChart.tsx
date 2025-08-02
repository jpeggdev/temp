"use client";

import React, { useMemo } from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
  CartesianGrid,
  ReferenceLine,
  Legend,
} from "recharts";
import { useTheme } from "@/context/ThemeContext";
import { PercentageOfNewCustomersByZipCodeDataItem } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";
import { generateColors } from "@/modules/stochastic/features/DashboardPage/utils/chart";
import { ChartNavigationSlider } from "@/modules/stochastic/features/DashboardPage/components/ChartNavigationSlider/ChartNavigationSlider";
import { ChartMetricCard } from "@/modules/stochastic/features/DashboardPage/components/ChartMetricCard/ChartMetricCard";
import ChartTooltipWithColoredYears from "@/modules/stochastic/features/DashboardPage/components/ChartTooltipWithColoredYears/ChartTooltipWithColoredYears";
import { ChartBarLegendWithColoredYear } from "@/modules/stochastic/features/DashboardPage/components/ChartBarLegendWithColoredYear/ChartBarLegendWithColoredYear";
import { useChartPagination } from "@/modules/stochastic/features/DashboardPage/hooks/useChartPagination";

type PercentageOfNewCustomersByZipCodeChartProps = {
  chartId?: string;
  initialData: PercentageOfNewCustomersByZipCodeDataItem[];
};

export const PercentageOfNewCustomersByZipCodeChart = ({
  initialData,
}: PercentageOfNewCustomersByZipCodeChartProps) => {
  if (!initialData || initialData.length === 0) return null;

  const { theme } = useTheme();
  const {
    page,
    setPage,
    totalPages,
    currentPageItems: paginatedData,
  } = useChartPagination(initialData, 10);

  const allYears = Object.keys(initialData[0]).filter(
    (key) => key !== "postalCode",
  );
  const colors = generateColors(allYears, theme);
  const postalCodes = initialData.map((d) => d.postalCode);

  const chartData = useMemo(() => {
    return paginatedData;
  }, [paginatedData]);

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Percentage of New Customers by ZIP Code
        </CardTitle>
      </CardHeader>

      <CardContent>
        <ResponsiveContainer height={500} width="100%">
          <BarChart
            data={chartData}
            margin={{ top: 20, right: 30, left: 60, bottom: 5 }}
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
              label={{
                value: "Percentage of New Customers (%)",
                angle: -90,
                position: "insideLeft",
                offset: 0,
                fill: theme === "dark" ? "#9CA3AF" : "#4B5563",
                style: { textAnchor: "middle", fontSize: 18 },
              }}
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              tickFormatter={(value) => `${value}%`}
            />
            <Tooltip
              content={
                <ChartTooltipWithColoredYears
                  colors={colors}
                  labelKey="postalCode"
                  labelPrefix="ZIP:"
                  valueSuffix="%"
                />
              }
            />
            <Legend content={<ChartBarLegendWithColoredYear />} />
            <ReferenceLine stroke="#ccc" y={0} />
            {allYears.map((year) => (
              <Bar
                dataKey={year}
                fill={colors[year]}
                isAnimationActive={true}
                key={year}
                name={year}
              />
            ))}
          </BarChart>
        </ResponsiveContainer>

        <div className="mt-8">
          <div className="text-sm text-gray-500 mb-3 text-center select-none font-semibold tracking-wide">
            Navigation Overview
          </div>

          <ChartNavigationSlider
            currentPage={page}
            itemsPerPage={10}
            onPageChange={setPage}
            postalCodes={postalCodes}
            theme={theme}
            totalPages={totalPages}
          />

          <div className="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-6 mt-6">
            <ChartMetricCard
              label="Current View"
              value={`${page + 1} of ${totalPages}`}
            />
            <ChartMetricCard label="ZIPs Shown" value={10} />
            <ChartMetricCard label="Total ZIPs" value={postalCodes.length} />
            <ChartMetricCard
              label="Years Tracked"
              value={`${allYears[0]}–${allYears.at(-1)}`}
            />
          </div>
        </div>
      </CardContent>
    </Card>
  );
};
