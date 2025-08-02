"use client";

import React from "react";

export default function TotalSalesNewCustomerByZipCodeAndYearChartSkeleton() {
  return (
    <div className="w-full bg-white dark:bg-secondary-dark rounded-md p-6 animate-pulse">
      <div className="h-8 w-3/5 bg-gray-300 dark:bg-gray-600 mb-6 rounded"></div>
      <div className="h-[500px] bg-gray-200 dark:bg-gray-700 rounded mb-8"></div>
      <div className="h-4 w-full bg-gray-300 dark:bg-gray-600 mb-2 rounded"></div>
      <div className="h-4 w-3/4 bg-gray-300 dark:bg-gray-600 mb-6 rounded"></div>
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div className="h-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div className="h-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div className="h-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div className="h-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
      </div>
    </div>
  );
}
