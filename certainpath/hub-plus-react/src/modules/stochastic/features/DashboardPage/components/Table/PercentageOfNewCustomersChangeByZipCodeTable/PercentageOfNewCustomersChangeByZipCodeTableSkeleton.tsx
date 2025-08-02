import React from "react";
import {
  Card,
  CardContent,
  CardHeader,
} from "@/components/Card/Card";

export const PercentageOfNewCustomersChangeByZipCodeTableSkeleton: React.FC =
  () => {
    const placeholderYears = ["2022", "2023", "2024"];
    const placeholderRows = new Array(5).fill(null);

    return (
      <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
        <CardHeader></CardHeader>
        <CardContent className="pb-10">
          <div className="w-full overflow-auto border border-gray-200 rounded-lg shadow-sm h-[450px]">
            <table className="w-full caption-bottom text-sm min-w-max">
              <thead className="[&_tr]:border-b">
                <tr className="bg-gray-50/75">
                  <th className="h-11 px-4 border-r border-gray-200 bg-gray-50/75" />
                  {placeholderYears.map((year) => (
                    <th
                      className="h-11 px-4 border-r border-gray-200 bg-gray-50/75"
                      colSpan={2}
                      key={year}
                    >
                      <div className="h-5 w-16 bg-gray-300 dark:bg-gray-700 rounded-md mx-auto" />
                    </th>
                  ))}
                </tr>
                <tr className="bg-white">
                  <th className="h-11 px-4 border-r border-gray-200">
                    <div className="h-5 w-20 bg-gray-300 dark:bg-gray-700 rounded-md mx-auto" />
                  </th>
                  {placeholderYears.map((year, index) => (
                    <React.Fragment key={year}>
                      <th className="h-11 px-4 border-r border-gray-200 whitespace-nowrap">
                        <div className="h-4 w-12 bg-gray-300 dark:bg-gray-700 rounded-md mx-auto" />
                      </th>
                      <th
                        className={`h-11 px-4 whitespace-nowrap ${
                          index === placeholderYears.length - 1
                            ? ""
                            : "border-r border-gray-200"
                        }`}
                      >
                        <div className="h-4 w-12 bg-gray-300 dark:bg-gray-700 rounded-md mx-auto" />
                      </th>
                    </React.Fragment>
                  ))}
                </tr>
              </thead>
              <tbody className="[&_tr:last-child]:border-0">
                {placeholderRows.map((_, rowIndex) => (
                  <tr className="border-b" key={rowIndex}>
                    <td className="px-2 py-2 border-r border-gray-200 whitespace-nowrap">
                      <div className="h-4 w-14 bg-gray-300 dark:bg-gray-700 rounded-md" />
                    </td>
                    {placeholderYears.map((_, index) => (
                      <React.Fragment key={index}>
                        <td className="px-2 py-2 whitespace-nowrap">
                          <div className="h-4 w-10 bg-gray-300 dark:bg-gray-700 rounded-md mx-auto" />
                        </td>
                        <td
                          className={`px-2 py-2 whitespace-nowrap ${
                            index === placeholderYears.length - 1
                              ? ""
                              : "border-r border-gray-200"
                          }`}
                        >
                          <div className="h-4 w-10 bg-gray-300 dark:bg-gray-700 rounded-md mx-auto" />
                        </td>
                      </React.Fragment>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    );
  };
