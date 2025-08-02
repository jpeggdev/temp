import React from "react";
import { Card, CardContent, CardHeader } from "@/components/Card/Card";

export const LifetimeValueByTierTableSkeleton: React.FC = () => {
  return (
    <Card className="w-full bg-white dark:bg-secondary-dark animate-pulse">
      <CardHeader></CardHeader>
      <CardContent className="pb-10">
        <div className="w-full overflow-auto border border-gray-200 rounded-lg shadow-sm">
          <table className="w-full caption-bottom text-sm min-w-max">
            <thead className="[&_tr]:border-b">
              <tr className="bg-gray-50/75">
                <th className="h-11 px-4 align-middle text-muted-foreground text-center border-r border-gray-200 bg-gray-50/75 w-12" />
                {[...Array(5)].map((_, index) => (
                  <th
                    className={`h-11 px-4 align-middle text-muted-foreground text-center border-gray-200 bg-gray-50/75 whitespace-nowrap ${
                      index === 4 ? "" : "border-r"
                    }`}
                    key={index}
                  >
                    <div className="bg-gray-200 dark:bg-gray-700 rounded h-6 w-24 mx-auto" />
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="[&_tr:last-child]:border-0">
              {[...Array(2)].map((_, rowIndex) => (
                <tr
                  className="border-b transition-colors hover:bg-muted/50"
                  key={rowIndex}
                >
                  <td className="px-4 py-2 text-left text-muted-foreground border-r border-gray-200 whitespace-nowrap font-semibold">
                    <div className="bg-gray-200 dark:bg-gray-700 rounded h-5 w-32" />
                  </td>
                  {[...Array(5)].map((_, cellIndex) => (
                    <td
                      className={`px-4 py-2 text-right text-black dark:text-white whitespace-nowrap ${
                        cellIndex === 4 ? "" : "border-r border-gray-200"
                      }`}
                      key={cellIndex}
                    >
                      <div className="bg-gray-200 dark:bg-gray-700 rounded h-5 w-16 mx-auto" />
                    </td>
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
