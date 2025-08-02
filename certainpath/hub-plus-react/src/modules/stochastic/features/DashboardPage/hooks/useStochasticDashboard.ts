import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  DashboardFiltersFormData,
  DashboardFiltersFormSchema,
} from "@/modules/stochastic/features/DashboardPage/hooks/dashboardFiltersFormSchema";
import { useCallback, useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { FetchDashboardDataRequest } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";
import { fetchStochasticDashboardChartsDataAction } from "@/modules/stochastic/features/DashboardPage/slice/StochasticDashboardSlice";
import { RootState } from "@/app/rootReducer";

export function useStochasticDashboard() {
  const dispatch = useDispatch();
  const defaultValues: DashboardFiltersFormData = {
    years: [],
    cities: [],
    trades: [],
  };

  const [activeTab, setActiveTab] = useState<"sales" | "customers">("sales");

  const { dashboardData, loading, error } = useSelector(
    (state: RootState) => state.stochasticDashboard,
  );

  const dashboardFiltersForm = useForm<DashboardFiltersFormData>({
    resolver: zodResolver(DashboardFiltersFormSchema),
    defaultValues,
    mode: "onChange",
  });

  const buildRequestData = useCallback((): FetchDashboardDataRequest => {
    const formValues = dashboardFiltersForm.getValues();

    return {
      scope: activeTab,
      years: (formValues.years || []).map((year) => year.name),
      trades: (formValues.trades || []).map((trade) => trade.id),
      cities: (formValues.cities || []).map((city) => city.name),
    };
  }, [dashboardFiltersForm, activeTab]);

  const refetchDiscounts = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(fetchStochasticDashboardChartsDataAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchDiscounts();
  }, [refetchDiscounts]);

  useEffect(() => {
    const subscription = dashboardFiltersForm.watch(() => {
      refetchDiscounts();
    });

    return () => subscription.unsubscribe();
  }, [dashboardFiltersForm, refetchDiscounts]);

  return {
    loading,
    error,
    dashboardData,
    dashboardFiltersForm,
    activeTab,
    setActiveTab,
  };
}
