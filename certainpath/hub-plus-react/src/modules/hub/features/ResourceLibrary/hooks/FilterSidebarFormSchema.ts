import { z } from "zod";

export const FilterSidebarFormSchema = z.object({
  searchTerm: z.string().optional(),
  isFavoriteOnly: z.boolean().optional().default(false),
  trades: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
      }),
    )
    .optional()
    .default([]),
  contentTypes: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
        resourceCount: z.number().optional(),
      }),
    )
    .optional()
    .default([]),
  employeeRoles: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
      }),
    )
    .optional()
    .default([]),
  categories: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
      }),
    )
    .optional()
    .default([]),
});

export type FilterSidebarFormData = z.infer<typeof FilterSidebarFormSchema>;
