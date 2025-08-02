import React from "react";
import { LegendProps } from "recharts";

/**
 * A reusable legend component designed for bar charts with year-based data.
 * Displays colored indicators with corresponding year labels.
 */
export const ChartBarLegendWithColoredYear = ({ payload }: LegendProps) => {
  if (!payload) return null;

  return (
    <ul className="flex flex-wrap justify-center gap-4 mt-4 text-sm font-medium">
      {payload.map((entry) => (
        <li className="flex items-center space-x-2" key={entry.value as string}>
          <span
            className="w-4 h-4 rounded-sm"
            style={{ backgroundColor: entry.color }}
          />
          <span style={{ color: entry.color }}>{entry.value}</span>
        </li>
      ))}
    </ul>
  );
};
