import React, { useMemo } from "react";
import {
  Card,
  CardContent,
  CardHeader,
} from "@/components/Card/Card";
import { PercentageOfNewCustomersChangeByZipCodeDataItem } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";

const extractYearsFromData = (
  data: PercentageOfNewCustomersChangeByZipCodeDataItem[],
): string[] => {
  const years = new Set<string>();
  data.forEach((item) => {
    Object.entries(item).forEach(([key, value]) => {
      if (key !== "postalCode" && typeof value === "object" && value !== null) {
        years.add(key);
      }
    });
  });
  return Array.from(years).sort();
};

interface PercentageOfNewCustomersByZipCodeTableProps {
  initialData: PercentageOfNewCustomersChangeByZipCodeDataItem[];
}

export const PercentageOfNewCustomersChangeByZipCodeTable: React.FC<
  PercentageOfNewCustomersByZipCodeTableProps
> = ({ initialData }) => {
  const allYears = useMemo(
    () => extractYearsFromData(initialData),
    [initialData],
  );

  return (
    <Card className="w-full bg-white dark:bg-secondary-dark">
      <CardHeader></CardHeader>
      <CardContent className="pb-10">
        <div className="w-full overflow-auto border border-gray-200 rounded-lg shadow-sm h-[450px]">
          <table className="w-full caption-bottom text-sm min-w-max">
            <thead className="[&_tr]:border-b">
              <tr className="bg-gray-50/75">
                <th className="h-11 px-4 align-middle font-semibold text-muted-foreground text-center border-r border-gray-200 bg-gray-50/75" />
                {allYears.map((year) => (
                  <th
                    className="h-11 px-4 align-middle font-semibold text-muted-foreground text-center border-r border-gray-200 bg-gray-50/75"
                    colSpan={2}
                    key={year}
                  >
                    {year}
                  </th>
                ))}
              </tr>
              <tr className="bg-white">
                <th className="h-11 px-4 align-middle font-semibold text-muted-foreground text-center border-r border-gray-200">
                  Zip Code
                </th>
                {allYears.map((year, index) => (
                  <React.Fragment key={year}>
                    <th className="h-11 px-4 align-middle font-semibold text-muted-foreground text-center border-gray-200 whitespace-nowrap">
                      %Change
                    </th>
                    <th
                      className={`h-11 px-4 align-middle font-semibold text-muted-foreground text-center border-gray-200 whitespace-nowrap ${
                        index === allYears.length - 1 ? "" : "border-r"
                      }`}
                    >
                      NC Count
                    </th>
                  </React.Fragment>
                ))}
              </tr>
            </thead>
            <tbody className="[&_tr:last-child]:border-0">
              {initialData.map((row, rowIndex) => (
                <tr
                  className="border-b transition-colors hover:bg-muted/50"
                  key={rowIndex}
                >
                  <td className="px-2 py-2 align-middle text-left border-r border-gray-200 whitespace-nowrap">
                    {row.postalCode}
                  </td>
                  {allYears.map((year, index) => {
                    const entry = row[year] as
                      | { ncCount: number; percentChange: number | null }
                      | undefined;

                    const change =
                      entry?.percentChange != null
                        ? `${entry.percentChange.toFixed(2)}%`
                        : "-";
                    const count = entry?.ncCount ?? "-";

                    return (
                      <React.Fragment key={year}>
                        <td className="px-2 py-2 text-right whitespace-nowrap">
                          {change}
                        </td>
                        <td
                          className={`px-2 py-2 text-right whitespace-nowrap ${
                            index === allYears.length - 1
                              ? ""
                              : "border-r border-gray-200"
                          }`}
                        >
                          {count}
                        </td>
                      </React.Fragment>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>
  );
};
