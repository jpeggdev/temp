import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
} from "@/components/Card/Card";
import { LifetimeValueByTierData } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";

interface Props {
  initialData?: LifetimeValueByTierData;
}

export const LifetimeValueByTierTable: React.FC<Props> = ({ initialData }) => {
  const chartData = initialData?.chartData ?? [];

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader>
      </CardHeader>
      <CardContent className="pb-10">
        <div className="w-full overflow-auto border border-gray-200 rounded-lg shadow-sm">
          <table className="w-full caption-bottom text-sm min-w-max">
            <thead className="[&_tr]:border-b">
              <tr className="bg-gray-50/75">
                <th className="h-11 px-4 align-middle text-muted-foreground text-center border-r border-gray-200 bg-gray-50/75" />
                {chartData.map((entry, index) => (
                  <th
                    className={`h-11 px-4 align-middle text-muted-foreground text-center border-gray-200 bg-gray-50/75 whitespace-nowrap ${
                      index === chartData.length - 1 ? "" : "border-r"
                    }`}
                    key={index}
                  >
                    {entry.tier} Households
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="[&_tr:last-child]:border-0">
              <tr className="border-b transition-colors hover:bg-muted/50">
                <td className="px-4 py-2 text-left text-muted-foreground border-r border-gray-200 whitespace-nowrap font-semibold">
                  Household Count
                </td>
                {chartData.map((entry, index) => (
                  <td
                    className={`px-4 py-2 text-right text-black dark:text-white whitespace-nowrap ${
                      index === chartData.length - 1
                        ? ""
                        : "border-r border-gray-200"
                    }`}
                    key={index}
                  >
                    {entry.householdCount.toLocaleString()}
                  </td>
                ))}
              </tr>
              <tr className="border-b transition-colors hover:bg-muted/50">
                <td className="px-4 py-2 text-left text-muted-foreground border-r border-gray-200 whitespace-nowrap font-semibold">
                  Total Sales
                </td>
                {chartData.map((entry, index) => (
                  <td
                    className={`px-4 py-2 text-right text-black dark:text-white whitespace-nowrap ${
                      index === chartData.length - 1
                        ? ""
                        : "border-r border-gray-200"
                    }`}
                    key={index}
                  >
                    {`$${entry.totalSales.toLocaleString()}`}
                  </td>
                ))}
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>
  );
};
