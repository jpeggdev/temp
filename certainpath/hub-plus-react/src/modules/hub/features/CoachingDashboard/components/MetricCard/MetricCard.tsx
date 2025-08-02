import React from "react";
import { useTheme } from "../../../../../../context/ThemeContext";

interface MetricCardProps {
  label: string;
  value: number;
  trend: "up" | "down";
  type: string;
}

export const MetricCard: React.FC<MetricCardProps> = ({
  label,
  value,
  trend,
  type,
}) => {
  const { theme } = useTheme();

  const getMetricStyle = () => {
    switch (label) {
      case "Club Member Conversion":
      case "Gross Margin":
        return {
          headerClass:
            theme === "dark" ? "bg-primary-dark/20" : "bg-primary/10",
          textClass: theme === "dark" ? "text-primary-light" : "text-primary",
        };
      case "Call Center Score":
        return {
          headerClass: theme === "dark" ? "bg-accent/20" : "bg-accent/10",
          textClass: "text-accent",
        };
      default:
        return {
          headerClass: theme === "dark" ? "bg-success/20" : "bg-success/10",
          textClass: "text-success",
        };
    }
  };

  const { headerClass, textClass } = getMetricStyle();

  return (
    <div
      className={`${theme === "dark" ? "bg-secondary" : "bg-white"} rounded shadow-sm h-full`}
    >
      <div className={`px-2 md:px-3 py-1 ${headerClass} rounded-t`}>
        <div className="text-xs md:text-sm line-clamp-2 min-h-[32px] md:min-h-[40px] dark:text-white">
          {label}
        </div>
      </div>
      <div className="p-2 flex items-center justify-between">
        <span
          className={`text-sm md:text-base lg:text-xl font-bold ${textClass} truncate`}
        >
          {type === "money"
            ? new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "USD",
                maximumFractionDigits: 0,
              }).format(value)
            : type === "score"
              ? value.toFixed(1)
              : `${value}%`}
        </span>
        <span className={`${textClass} ml-1 flex-shrink-0`}>
          {trend === "up" ? "▲" : "▼"}
        </span>
      </div>
    </div>
  );
};
