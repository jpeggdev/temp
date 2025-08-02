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
import { TotalSalesNewVsExistingCustomerChartDataItem } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesNewVsExistingCustomerChartData/types";
import { formatCurrency } from "@/modules/stochastic/features/DashboardPage/utils/chart";

type ChartProps = {
  chartId?: string;
  initialData: TotalSalesNewVsExistingCustomerChartDataItem[];
};

export default function TotalSalesNewVsExistingCustomerChart({
  initialData,
}: ChartProps) {
  const { theme } = useTheme();

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
        <CardTitle className="text-fontColor dark:text-light">
          Total Sales New vs. Existing Customer
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ResponsiveContainer height={500} width="100%">
          <BarChart
            data={initialData}
            margin={{
              bottom: 5,
              left: 45,
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
              labelFormatter={(label) => `Year: ${label}`}
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
