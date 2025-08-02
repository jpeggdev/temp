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
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Legend,
} from "recharts";
import { useTheme } from "@/context/ThemeContext";
import { ChartNavigationSlider } from "@/modules/stochastic/features/DashboardPage/components/ChartNavigationSlider/ChartNavigationSlider";
import { formatCurrency, generateColors } from "../../../utils/chart";
import { ChartMetricCard } from "@/modules/stochastic/features/DashboardPage/components/ChartMetricCard/ChartMetricCard";
import { TotalSalesNewCustomerByZipCodeAndYearDataItem } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";
import { ChartBarLegendWithColoredYear } from "@/modules/stochastic/features/DashboardPage/components/ChartBarLegendWithColoredYear/ChartBarLegendWithColoredYear";
import ChartTooltipWithColoredYears from "@/modules/stochastic/features/DashboardPage/components/ChartTooltipWithColoredYears/ChartTooltipWithColoredYears";
import { useChartPagination } from "@/modules/stochastic/features/DashboardPage/hooks/useChartPagination";

interface TotalSalesNewCustomerByZipCodeAndYearChartProps {
  chartId?: string;
  initialData: TotalSalesNewCustomerByZipCodeAndYearDataItem[];
}

export default function TotalSalesNewCustomerByZipCodeAndYearChart({
  initialData,
}: TotalSalesNewCustomerByZipCodeAndYearChartProps) {
  const { theme } = useTheme();

  const years = Array.from(new Set(initialData.map((d) => d.year))).sort();

  const postalCodes = Array.from(
    new Set(initialData.map((d) => d.postalCode)),
  ) as string[];
  postalCodes.sort();

  const colors = generateColors(years, theme);

  const {
    page,
    setPage,
    totalPages,
    currentPageItems: currentPostalCodes,
  } = useChartPagination(postalCodes, 10);

  const chartData = useMemo(() => {
    return currentPostalCodes.map((zip) => {
      const entry: Record<string, number | string> = { postalCode: zip };
      initialData.forEach(({ postalCode, year, sales }) => {
        if (postalCode === zip) {
          entry[year] = sales;
        }
      });
      return entry;
    });
  }, [initialData, currentPostalCodes]);

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Total Sales New Customer by ZIP Code & Year
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ResponsiveContainer height={500} width="100%">
          <BarChart
            data={chartData}
            margin={{ top: 20, right: 30, left: 45, bottom: 5 }}
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
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              tickFormatter={formatCurrency}
            />
            <Tooltip
              content={
                <ChartTooltipWithColoredYears
                  colors={colors}
                  labelKey="postalCode"
                  labelPrefix="ZIP:"
                  valueSuffix=""
                />
              }
            />
            <Legend content={<ChartBarLegendWithColoredYear />} />
            {years.map((year) => (
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
              value={`${years[0]}–${years.at(-1)}`}
            />
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
