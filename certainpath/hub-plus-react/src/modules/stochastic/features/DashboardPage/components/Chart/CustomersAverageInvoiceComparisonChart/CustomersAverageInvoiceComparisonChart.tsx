import React from "react";
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  Tooltip,
  Legend,
  ResponsiveContainer,
  CartesianGrid,
  TooltipProps,
} from "recharts";
import {
  NameType,
  ValueType,
} from "recharts/types/component/DefaultTooltipContent";
import { useTheme } from "@/context/ThemeContext";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";
import { formatCurrency } from "../../../utils/chart";
import { CustomersAverageInvoiceComparisonData } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";

type Props = {
  initialData?: CustomersAverageInvoiceComparisonData;
};

const currencyFormatter = (value?: number) =>
  typeof value === "number"
    ? `$${value.toLocaleString(undefined, { maximumFractionDigits: 0 })}`
    : "N/A";

const CustomTooltip: React.FC<TooltipProps<ValueType, NameType>> = ({
  active,
  payload,
  label,
}) => {
  const { theme } = useTheme();

  if (active && payload && payload.length) {
    const newCustomer = payload.find((p) => p.dataKey === "newCustomerAvg");
    const repeatCustomer = payload.find(
      (p) => p.dataKey === "repeatCustomerAvg",
    );

    const styles = {
      backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
      borderColor: theme === "dark" ? "#374151" : "#E5E7EB",
      color: theme === "dark" ? "#F3F4F6" : "#1F2937",
    };

    return (
      <div
        className="p-2 border rounded text-sm shadow space-y-1"
        style={styles}
      >
        <div>
          <strong>{label}</strong>
        </div>
        {newCustomer && (
          <div style={{ color: "#3b82f6" }}>
            <strong>
              New Customer: {currencyFormatter(newCustomer.value as number)}
            </strong>
          </div>
        )}
        {repeatCustomer && (
          <div style={{ color: "#f97316" }}>
            <strong>
              Repeat Customer:{" "}
              {currencyFormatter(repeatCustomer.value as number)}
            </strong>
          </div>
        )}
      </div>
    );
  }

  return null;
};

const CustomersAverageInvoiceComparisonChart: React.FC<Props> = ({
  initialData,
}) => {
  const { theme } = useTheme();

  const newAvg = initialData?.avgSales?.newCustomerAvg;
  const repeatAvg = initialData?.avgSales?.repeatCustomerAvg;
  const chartData = initialData?.chartData ?? [];

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Average Invoice Comparison
        </CardTitle>
        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Compares average invoice amounts between new and repeat customers
        </p>
      </CardHeader>
      <CardContent>
        <div className="flex flex-col gap-1 text-sm font-medium mb-6">
          <span>
            New Customer Average:{" "}
            <span className="text-blue-600 dark:text-blue-400 text-base font-semibold">
              {currencyFormatter(newAvg)}
            </span>
          </span>
          <span>
            Repeat Customer Average:{" "}
            <span className="text-orange-600 dark:text-orange-400 text-base font-semibold">
              {currencyFormatter(repeatAvg)}
            </span>
          </span>
        </div>
        <ResponsiveContainer height={450} width="100%">
          <LineChart
            data={chartData}
            margin={{ top: 20, right: 30, left: 45, bottom: 5 }}
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
            <Tooltip content={<CustomTooltip />} />
            <Legend />
            <Line
              dataKey="newCustomerAvg"
              dot={{ r: 4 }}
              name="New Customer Average"
              stroke="#3b82f6"
              strokeWidth={2}
              type="monotone"
            />
            <Line
              dataKey="repeatCustomerAvg"
              dot={{ r: 4 }}
              name="Repeat Customer Average"
              stroke="#f97316"
              strokeWidth={2}
              type="monotone"
            />
          </LineChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
};

export default CustomersAverageInvoiceComparisonChart;
