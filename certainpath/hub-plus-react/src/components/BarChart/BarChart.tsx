import React from "react";
import {
  BarChart as RechartsBarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  ReferenceLine,
} from "recharts";

interface DataPoint {
  name: string;
  [key: string]: number | string;
}

interface BarConfig {
  dataKey: string;
  fill: string;
}

interface BarChartProps {
  data: DataPoint[];
  bars: BarConfig[];
  width?: number | string;
  height?: number | string;
  referenceLine?: number | null;
}

const BarChart: React.FC<BarChartProps> = ({
  data,
  bars,
  width = "100%",
  height = 400,
  referenceLine = null,
}) => {
  return (
    <ResponsiveContainer height={height} width={width}>
      <RechartsBarChart data={data}>
        <CartesianGrid strokeDasharray="3 3" />
        <XAxis dataKey="name" />
        <YAxis />
        <Tooltip />
        <Legend />
        {referenceLine && (
          <ReferenceLine
            stroke="orange"
            strokeDasharray="3 3"
            y={referenceLine ?? undefined}
          />
        )}

        {bars.map((bar, index) => (
          <Bar dataKey={bar.dataKey} fill={bar.fill} key={index} />
        ))}
      </RechartsBarChart>
    </ResponsiveContainer>
  );
};

export default BarChart;
