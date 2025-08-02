"use client";

import React from "react";
import { Form } from "@/components/ui/form";
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { EntityMultiSelect } from "@/components/EntityMultiSelect/EntityMultiSelect";
import { fetchCityFilterOptions } from "@/modules/stochastic/features/DashboardPage/api/fetchCityFilterOptions/fetchCityFilterOptionsApi";
import { CityFilterOption } from "@/modules/stochastic/features/DashboardPage/api/fetchCityFilterOptions/types";
import { fetchTradesFilterOptions } from "@/modules/stochastic/features/DashboardPage/api/fetchTradeFilterOptions/fetchTradeFilterOptionsApi";
import { fetchYearFilterOptions } from "@/modules/stochastic/features/DashboardPage/api/fetchYearFilterOptions/fetchYearFilterOptionsApi";
import { YearFilterOption } from "@/modules/stochastic/features/DashboardPage/api/fetchYearFilterOptions/types";
import { TradeFilterOption } from "@/modules/stochastic/features/DashboardPage/api/fetchTradeFilterOptions/types";
import { DashboardFiltersFormData } from "@/modules/stochastic/features/DashboardPage/hooks/dashboardFiltersFormSchema";
import { UseFormReturn } from "react-hook-form";

interface StochasticDashboardFiltersProps {
  chartFiltersForm: UseFormReturn<DashboardFiltersFormData>;
}

export const StochasticDashboardFilters: React.FC<
  StochasticDashboardFiltersProps
> = ({ chartFiltersForm }) => {
  const { control, handleSubmit } = chartFiltersForm;

  return (
    <div className="mb-1">
      <div className="grid grid-cols-1 gap-4">
        <Form {...chartFiltersForm}>
          <form
            className="space-y-8 pb-10 bg-white"
            onSubmit={handleSubmit(() => {})}
          >
            <FormField
              control={control}
              name="cities"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Cities</FormLabel>
                  <FormControl>
                    <EntityMultiSelect
                      entityNamePlural="Cities"
                      entityNameSingular="City"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await fetchCityFilterOptions({
                          searchTerm,
                          page,
                          pageSize,
                          sortBy: "city",
                          sortOrder: "ASC",
                        });
                        const { data } = response;
                        const totalCount =
                          response.meta?.totalCount ?? data.length;

                        return {
                          data: data.map((cfo: CityFilterOption) => ({
                            id: String(cfo.id),
                            name: cfo.name,
                          })),
                          totalCount,
                        };
                      }}
                      isFullWidth={true}
                      onChange={field.onChange}
                      value={field.value || []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={control}
              name="trades"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Trades</FormLabel>
                  <FormControl>
                    <EntityMultiSelect
                      entityNamePlural="Trades"
                      entityNameSingular="Trade"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await fetchTradesFilterOptions({
                          searchTerm,
                          page,
                          pageSize,
                          sortBy: "name",
                          sortOrder: "ASC",
                        });
                        const { data } = response;
                        const totalCount =
                          response.meta?.totalCount ?? data.length;

                        return {
                          data: data.map((tfo: TradeFilterOption) => ({
                            id: String(tfo.id),
                            name: tfo.name,
                          })),
                          totalCount,
                        };
                      }}
                      isFullWidth={true}
                      onChange={field.onChange}
                      value={field.value || []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={control}
              name="years"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Years</FormLabel>
                  <FormControl>
                    <EntityMultiSelect
                      entityNamePlural="Years"
                      entityNameSingular="Year"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await fetchYearFilterOptions({
                          searchTerm,
                          page,
                          pageSize,
                          sortBy: "year",
                          sortOrder: "DESC",
                        });
                        const { data } = response;
                        const totalCount =
                          response.meta?.totalCount ?? data.length;

                        return {
                          data: data.map((yfo: YearFilterOption) => ({
                            id: String(yfo.id),
                            name: yfo.name,
                          })),
                          totalCount,
                        };
                      }}
                      isFullWidth={true}
                      onChange={field.onChange}
                      value={field.value || []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </form>
        </Form>
      </div>
    </div>
  );
};
