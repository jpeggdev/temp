import React, { useState } from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";
import { Badge } from "@/components/Badge/Badge";
import { Button } from "@/components/Button/Button";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from "@/components/Command/Command";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/Popover/Popover";
import { Check, ChevronsUpDown } from "lucide-react";
import { cn } from "@/utils/utils";
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
  BarChart,
} from "recharts";
import { useTheme } from "@/context/ThemeContext";
import { filterOptions } from "@/mocks/mockData";

type MultiSelectProps = {
  onChange: (selected: string[]) => void;
  options: string[];
  placeholder: string;
  selected: string[];
};

function MultiSelect({
  options,
  selected = [],
  onChange,
  placeholder,
}: MultiSelectProps) {
  const [open, setOpen] = useState(false);
  const safeSelected = Array.isArray(selected) ? selected : [];

  return (
    <Popover onOpenChange={setOpen} open={open}>
      <PopoverTrigger asChild>
        <Button
          aria-expanded={open}
          className="w-[200px] justify-between bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600"
          role="combobox"
          variant="outline"
        >
          {safeSelected.length > 0
            ? `${safeSelected.length} selected`
            : placeholder}
          <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-[200px] p-0 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600">
        <Command className="bg-transparent" shouldFilter={false}>
          <CommandInput
            className="text-gray-900 dark:text-gray-100"
            placeholder={`Search ${placeholder.toLowerCase()}...`}
          />
          <CommandEmpty className="text-gray-500 dark:text-gray-400">
            No {placeholder.toLowerCase()} found.
          </CommandEmpty>
          <CommandGroup>
            {options.map((option) => (
              <CommandItem
                className="text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                key={option}
                onSelect={() => {
                  const newSelected = safeSelected.includes(option)
                    ? safeSelected.filter((item) => item !== option)
                    : [...safeSelected, option];
                  onChange(newSelected);
                }}
                value={option}
              >
                <Check
                  className={cn(
                    "mr-2 h-4 w-4",
                    safeSelected.includes(option) ? "opacity-100" : "opacity-0",
                  )}
                />
                {option}
              </CommandItem>
            ))}
          </CommandGroup>
        </Command>
      </PopoverContent>
    </Popover>
  );
}

type ChartDataItem = {
  days?: string;
  month?: string;
  salesPercentage?: number;
  totalSales?: number;
  [key: string]: string | number | undefined;
};

type AlphaChartProps = {
  chartId?: string;
  initialData: ChartDataItem[];
  type?:
    | "area"
    | "bar"
    | "customer"
    | "line"
    | "pie"
    | "topZips"
    | "yearly"
    | "zip";
};

type TopZipItem = {
  cumulativePercentage: number;
  totalSales: number;
  zipCode: string;
};

export default function AlphaChart({
  type = "line",
  initialData,
}: AlphaChartProps) {
  const [data, setData] = useState(initialData);
  const [filters, setFilters] = useState<{
    cities: string[];
    years: string[];
    trades: string[];
  }>({
    cities: [],
    years: [],
    trades: [],
  });

  const { theme } = useTheme();

  const handleFilterChange = (
    filterType: "cities" | "years" | "trades",
    values: string[],
  ) => {
    setFilters((prev) => ({ ...prev, [filterType]: values }));
    setData(
      initialData.map((item) => ({
        ...item,
        totalSales: item.totalSales
          ? Math.floor(item.totalSales * (0.5 + Math.random()))
          : 0,
        salesPercentage: item.salesPercentage
          ? Math.floor(item.salesPercentage * (0.5 + Math.random()))
          : 0,
      })),
    );
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(value);
  };

  const formatPercentage = (value: number) => {
    return `${value}%`;
  };

  const YEAR_COLORS = {
    "2018": { light: "#FCD34D", dark: "#FBBF24" },
    "2019": { light: "#92400E", dark: "#B45309" },
    "2020": { light: "#EF4444", dark: "#F87171" },
    "2021": { light: "#3B82F6", dark: "#60A5FA" },
    "2022": { light: "#22C55E", dark: "#34D399" },
    "2023": { light: "#F97316", dark: "#FB923C" },
  };

  const getProgressBarColor = (percentage: number, isDark: boolean) => {
    if (percentage <= 8) return isDark ? "bg-blue-400" : "bg-blue-500";
    if (percentage <= 18) return isDark ? "bg-green-400" : "bg-green-500";
    if (percentage <= 26) return isDark ? "bg-yellow-400" : "bg-yellow-500";
    return isDark ? "bg-red-400" : "bg-red-500";
  };

  if (type === "yearly") {
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
              data={data}
              margin={{
                top: 20,
                right: 30,
                left: 20,
                bottom: 5,
              }}
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
                contentStyle={{
                  backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
                  borderColor: theme === "dark" ? "#374151" : "#E5E7EB",
                  color: theme === "dark" ? "#F3F4F6" : "#1F2937",
                }}
                formatter={formatCurrency}
              />
              <Legend />
              {Object.keys(YEAR_COLORS).map((year) => (
                <Bar
                  dataKey={year}
                  fill={
                    YEAR_COLORS[year as keyof typeof YEAR_COLORS][
                      theme === "dark" ? "dark" : "light"
                    ]
                  }
                  key={year}
                  name={year}
                />
              ))}
            </BarChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>
    );
  }

  if (type === "zip") {
    const filteredData = React.useMemo(() => {
      let filtered = [...data];

      // Filter by zip code if there's input
      const zipInput = document.querySelector(
        'input[placeholder="Enter zip code"]',
      ) as HTMLInputElement;
      if (zipInput && zipInput.value) {
        filtered = filtered.filter((item) =>
          item.zipCode?.toString().includes(zipInput.value),
        );
      }

      // Filter by selected years
      if (filters.years.length > 0) {
        filtered = filtered.map((item) => {
          const filteredItem: Record<string, string | number> = {
            zipCode: item.zipCode ?? "",
          };
          filters.years.forEach((year) => {
            const value = item[year];
            if (value !== undefined) {
              filteredItem[year] = value;
            }
          });
          return filteredItem;
        });
      }

      return filtered;
    }, [data, filters.years, filters.trades]);

    return (
      <Card className="w-full bg-white dark:bg-secondary-dark">
        <CardHeader>
          <CardTitle className="text-fontColor dark:text-light">
            Total Sales by Zip
          </CardTitle>
          <p className="text-sm text-gray-500">
            Sales data across zip codes from 2018 to 2023
          </p>
        </CardHeader>
        <CardContent>
          <div className="flex flex-wrap gap-4 mb-4">
            <input
              className="p-2 border rounded-md dark:bg-gray-800 dark:text-gray-100"
              onChange={() => {
                // Force re-render to trigger the useMemo
                setData([...initialData]);
              }}
              placeholder="Enter zip code"
              type="text"
            />
            <MultiSelect
              onChange={(values) => handleFilterChange("years", values)}
              options={filterOptions.years}
              placeholder="Select Years"
              selected={filters.years}
            />
            <MultiSelect
              onChange={(values) => handleFilterChange("trades", values)}
              options={filterOptions.trades}
              placeholder="Select Trades"
              selected={filters.trades}
            />
          </div>
          <ResponsiveContainer height={500} width="100%">
            <BarChart
              data={filteredData}
              margin={{
                top: 20,
                right: 30,
                left: 20,
                bottom: 5,
              }}
            >
              <CartesianGrid
                stroke={theme === "dark" ? "#374151" : "#e5e7eb"}
                strokeDasharray="3 3"
              />
              <XAxis
                dataKey="zipCode"
                stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              />
              <YAxis
                stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
                tickFormatter={formatCurrency}
              />
              <Tooltip
                contentStyle={{
                  backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
                  borderColor: theme === "dark" ? "#374151" : "#E5E7EB",
                  color: theme === "dark" ? "#F3F4F6" : "#1F2937",
                }}
                formatter={formatCurrency}
              />
              <Legend />
              {Object.keys(YEAR_COLORS)
                .filter(
                  (year) =>
                    filters.years.length === 0 || filters.years.includes(year),
                )
                .map((year) => (
                  <Bar
                    dataKey={year}
                    fill={
                      YEAR_COLORS[year as keyof typeof YEAR_COLORS][
                        theme === "dark" ? "dark" : "light"
                      ]
                    }
                    key={year}
                    name={year}
                  />
                ))}
            </BarChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>
    );
  }

  if (type === "customer") {
    return (
      <Card className="w-full bg-white dark:bg-secondary-dark">
        <CardHeader>
          <CardTitle className="text-fontColor dark:text-light">
            Total House File, New Customer Sales by Year
          </CardTitle>
        </CardHeader>
        <CardContent>
          <ResponsiveContainer height={500} width="100%">
            <BarChart
              data={data}
              margin={{
                bottom: 5,
                left: 20,
                right: 30,
                top: 20,
              }}
            >
              <CartesianGrid
                stroke={theme === "dark" ? "#374151" : "#e5e7eb"}
                strokeDasharray="3 3"
              />
              <XAxis
                dataKey="year"
                stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              />
              <YAxis
                stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
                tickFormatter={formatCurrency}
              />
              <Tooltip
                contentStyle={{
                  backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
                  borderColor: theme === "dark" ? "#374151" : "#E5E7EB",
                  color: theme === "dark" ? "#F3F4F6" : "#1F2937",
                }}
                formatter={formatCurrency}
              />
              <Legend />
              <Bar
                dataKey="NC"
                fill={theme === "dark" ? "#F87171" : "#EF4444"}
                name="NC"
                stackId="a"
              />
              <Bar
                dataKey="HF"
                fill={theme === "dark" ? "#FB923C" : "#F59E0B"}
                name="HF"
                stackId="a"
              />
            </BarChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>
    );
  }

  if (type === "topZips") {
    return (
      <Card className="w-full bg-white dark:bg-secondary-dark">
        <CardHeader>
          <CardTitle className="text-fontColor dark:text-light">
            Top 10 Zips by Revenue
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b dark:border-gray-700">
                  <th className="py-3 text-left text-gray-500 dark:text-gray-400">
                    Zip Code
                  </th>
                  <th className="py-3 text-left text-gray-500 dark:text-gray-400">
                    Total Sales
                  </th>
                  <th className="py-3 text-left text-gray-500 dark:text-gray-400">
                    Cumulative Sales
                  </th>
                </tr>
              </thead>
              <tbody>
                {(data as TopZipItem[]).map((item) => (
                  <tr
                    className="border-b dark:border-gray-700"
                    key={item.zipCode}
                  >
                    <td className="py-3">
                      <span className="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800">
                        {item.zipCode}
                      </span>
                    </td>
                    <td className="py-3">{formatCurrency(item.totalSales)}</td>
                    <td className="py-3">
                      <div className="flex items-center gap-2">
                        <div className="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                          <div
                            className={`${getProgressBarColor(item.cumulativePercentage, theme === "dark")} h-2 rounded-full`}
                            style={{ width: `${item.cumulativePercentage}%` }}
                          />
                        </div>
                        <span>{item.cumulativePercentage}%</span>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="w-full max-w-4xl bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Alpha Chart (Days)
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="flex flex-wrap gap-4 mb-4">
          <MultiSelect
            onChange={(values) => handleFilterChange("cities", values)}
            options={filterOptions.cities}
            placeholder="Select Cities"
            selected={filters.cities}
          />
          <MultiSelect
            onChange={(values) => handleFilterChange("years", values)}
            options={filterOptions.years}
            placeholder="Select Years"
            selected={filters.years}
          />
          <MultiSelect
            onChange={(values) => handleFilterChange("trades", values)}
            options={filterOptions.trades}
            placeholder="Select Trades"
            selected={filters.trades}
          />
        </div>
        <div className="flex flex-wrap gap-2 mb-4">
          {filters.cities.map((city) => (
            <Badge key={city} variant="secondary">
              {city}
            </Badge>
          ))}
          {filters.years.map((year) => (
            <Badge key={year} variant="secondary">
              {year}
            </Badge>
          ))}
          {filters.trades.map((trade) => (
            <Badge key={trade} variant="secondary">
              {trade}
            </Badge>
          ))}
        </div>
        <ResponsiveContainer height={400} width="100%">
          <ComposedChart
            data={data}
            margin={{
              top: 5,
              right: 30,
              left: 20,
              bottom: 5,
            }}
          >
            <CartesianGrid
              stroke={theme === "dark" ? "#374151" : "#e5e7eb"}
              strokeDasharray="3 3"
            />
            <XAxis
              dataKey="days"
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
