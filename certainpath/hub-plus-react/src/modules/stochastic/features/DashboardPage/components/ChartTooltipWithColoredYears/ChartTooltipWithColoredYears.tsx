import React from "react";
import { useTheme } from "@/context/ThemeContext";

interface ChartDataItem {
  postalCode: string;
  [key: string]: string | number;
}

interface ColoredYearTooltipProps {
  active?: boolean;
  payload?: { payload: ChartDataItem }[];
  colors: Record<string, string>;
  labelKey?: keyof ChartDataItem;
  labelPrefix?: string;
  valueSuffix?: string;
}

const ChartTooltipWithColoredYears = ({
  active,
  payload,
  colors,
  labelKey = "postalCode",
  labelPrefix = "ZIP:",
  valueSuffix = "%",
}: ColoredYearTooltipProps) => {
  const { theme } = useTheme();

  if (!active || !payload?.length) return null;

  const data = payload[0].payload;

  const entries = Object.entries(data)
    .filter(([key]) => key !== labelKey)
    .sort(([a], [b]) => a.localeCompare(b));

  const styles = {
    backgroundColor: theme === "dark" ? "#1F2937" : "#FFFFFF",
    borderColor: theme === "dark" ? "#374151" : "#E5E7EB",
    color: theme === "dark" ? "#F3F4F6" : "#1F2937",
  };

  return (
    <div className="p-2 border rounded text-sm shadow space-y-1" style={styles}>
      <div>
        <strong>{labelPrefix}</strong> {data[labelKey]}
      </div>
      {entries.map(([year, value]) => (
        <div key={year} style={{ color: colors[year] ?? styles.color }}>
          <strong>{year}:</strong> {value}
          {valueSuffix}
        </div>
      ))}
    </div>
  );
};

export default ChartTooltipWithColoredYears;
