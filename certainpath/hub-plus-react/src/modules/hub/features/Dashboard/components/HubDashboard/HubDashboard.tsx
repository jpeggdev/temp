import React from "react";
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  PieChart,
  Pie,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from "recharts";
import { useTheme } from "../../../../../../context/ThemeContext";

interface ChartProps {
  chartId: string;
  type?: "line" | "bar" | "pie" | "area";
  data: Array<{ name: string; value: number }>;
}

const AlphaChart: React.FC<ChartProps> = ({ chartId, type = "line", data }) => {
  const { theme } = useTheme();

  const chartColors = {
    primary: theme === "dark" ? "#D94C65" : "#B21E34",
    secondary: theme === "dark" ? "#3A2C6A" : "#17084A",
    background: theme === "dark" ? "#0e0637" : "#ffffff",
    text: theme === "dark" ? "#e5e5e5" : "#000000",
    grid: theme === "dark" ? "#818181" : "#e5e5e5",
  };

  const renderChart = () => {
    switch (type) {
      case "bar":
        return (
          <ResponsiveContainer height="100%" width="100%">
            <BarChart data={data}>
              <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" />
              <XAxis dataKey="name" stroke={chartColors.text} />
              <YAxis stroke={chartColors.text} />
              <Tooltip
                contentStyle={{
                  backgroundColor: chartColors.background,
                  border: `1px solid ${chartColors.grid}`,
                  color: chartColors.text,
                }}
              />
              <Bar dataKey="value" fill={chartColors.primary} />
            </BarChart>
          </ResponsiveContainer>
        );

      case "pie":
        return (
          <ResponsiveContainer height="100%" width="100%">
            <PieChart>
              <Pie
                cx="50%"
                cy="50%"
                data={data}
                dataKey="value"
                fill={chartColors.primary}
                nameKey="name"
              />
              <Tooltip
                contentStyle={{
                  backgroundColor: chartColors.background,
                  border: `1px solid ${chartColors.grid}`,
                  color: chartColors.text,
                }}
              />
            </PieChart>
          </ResponsiveContainer>
        );

      case "area":
        return (
          <ResponsiveContainer height="100%" width="100%">
            <AreaChart data={data}>
              <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" />
              <XAxis dataKey="name" stroke={chartColors.text} />
              <YAxis stroke={chartColors.text} />
              <Tooltip
                contentStyle={{
                  backgroundColor: chartColors.background,
                  border: `1px solid ${chartColors.grid}`,
                  color: chartColors.text,
                }}
              />
              <Area
                dataKey="value"
                fill={chartColors.primary}
                fillOpacity={0.6}
                stroke={chartColors.secondary}
                type="monotone"
              />
            </AreaChart>
          </ResponsiveContainer>
        );

      default: // line chart
        return (
          <ResponsiveContainer height="100%" width="100%">
            <LineChart data={data}>
              <CartesianGrid stroke={chartColors.grid} strokeDasharray="3 3" />
              <XAxis dataKey="name" stroke={chartColors.text} />
              <YAxis stroke={chartColors.text} />
              <Tooltip
                contentStyle={{
                  backgroundColor: chartColors.background,
                  border: `1px solid ${chartColors.grid}`,
                  color: chartColors.text,
                }}
              />
              <Line
                dataKey="value"
                stroke={chartColors.primary}
                strokeWidth={2}
                type="monotone"
              />
            </LineChart>
          </ResponsiveContainer>
        );
    }
  };

  return (
    <div className="w-full h-full flex flex-col bg-white dark:bg-secondary-dark rounded-lg shadow-sm p-4">
      <h3 className="text-lg font-semibold mb-4 text-fontColor dark:text-light">{`Chart ${chartId}`}</h3>
      <div className="flex-1">{renderChart()}</div>
    </div>
  );
};

export default AlphaChart;
