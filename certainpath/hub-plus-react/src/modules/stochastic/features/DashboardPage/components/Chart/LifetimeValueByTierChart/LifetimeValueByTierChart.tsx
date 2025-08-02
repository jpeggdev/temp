import React from "react";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
  TooltipProps,
  CartesianGrid,
  Legend,
} from "recharts";
import {
  NameType,
  ValueType,
} from "recharts/types/component/DefaultTooltipContent";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/Card/Card";
import { useTheme } from "@/context/ThemeContext";
import { LifetimeValueByTierData } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";

const CustomTooltip: React.FC<TooltipProps<ValueType, NameType>> = ({
  active,
  payload,
  label,
}) => {
  const { theme } = useTheme();

  if (!active || !payload || payload.length === 0) return null;

  const item = payload[0];
  const value = item?.value;

  return (
    <div
      className="p-2 text-sm rounded shadow"
      style={{
        backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
        border: `1px solid ${theme === "dark" ? "#374151" : "#E5E7EB"}`,
        color: theme === "dark" ? "#F3F4F6" : "#1F2937",
      }}
    >
      <div className="font-medium">
        <strong>Tier: {label ?? "N/A"}</strong>
      </div>
      <div style={{ color: "#3B82F6", fontWeight: 500 }}>
        <strong>
          Households:{" "}
          {typeof value === "number" ? value.toLocaleString() : "N/A"}
        </strong>
      </div>
    </div>
  );
};

interface LifetimeValueByTierChartProps {
  initialData?: LifetimeValueByTierData;
}

export default function LifetimeValueByTierChart({
  initialData,
}: LifetimeValueByTierChartProps) {
  const { theme } = useTheme();

  const totalHouseholds = initialData?.totalHouseholdsCount;
  const chartData = initialData?.chartData ?? [];

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle className="text-fontColor dark:text-light">
            Lifetime Value by Tier
          </CardTitle>
          <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Customer count segmented by spending tiers
          </p>
        </div>
        {typeof totalHouseholds === "number" && (
          <div className="px-3 py-1 mr-[30px] rounded-full bg-gray-100 dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-100">
            <b>{totalHouseholds.toLocaleString()} Households</b>
          </div>
        )}
      </CardHeader>
      <CardContent>
        <ResponsiveContainer height={500} width="100%">
          <BarChart
            data={chartData}
            margin={{ top: 5, right: 30, left: 45, bottom: 10 }}
          >
            <CartesianGrid
              stroke={theme === "dark" ? "#374151" : "#e5e7eb"}
              strokeDasharray="3 3"
            />
            <XAxis
              angle={-35}
              dataKey="tier"
              height={50}
              interval={0}
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              textAnchor="end"
              tick={{ fontSize: 14 }}
            />
            <YAxis
              stroke={theme === "dark" ? "#9CA3AF" : "#4B5563"}
              tickFormatter={(value) =>
                typeof value === "number" ? value.toLocaleString() : "N/A"
              }
            />
            <Tooltip content={<CustomTooltip />} />
            <Legend
              wrapperStyle={{
                paddingTop: "60px",
                textAlign: "center",
              }}
            />
            <Bar
              dataKey="householdCount"
              fill="#3B82F6"
              name="Household Count"
            />
          </BarChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
}
