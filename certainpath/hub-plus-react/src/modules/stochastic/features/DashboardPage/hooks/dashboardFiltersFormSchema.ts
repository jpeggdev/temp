import { z } from "zod";

export const CompanySchema = z.object({
  id: z.number(),
  name: z.string(),
});

export const CitiesSchema = z.object({
  id: z.string(),
  name: z.string(),
});

export const TradesSchema = z.object({
  id: z.string(),
  name: z.string(),
});

export const YearsSchema = z.object({
  id: z.string(),
  name: z.string(),
});

export const DashboardFiltersFormSchema = z.object({
  company: CompanySchema.optional(),
  cities: z.array(CitiesSchema).optional(),
  trades: z.array(TradesSchema).optional(),
  years: z.array(YearsSchema).optional(),
});

export type DashboardFiltersFormData = z.infer<
  typeof DashboardFiltersFormSchema
>;
