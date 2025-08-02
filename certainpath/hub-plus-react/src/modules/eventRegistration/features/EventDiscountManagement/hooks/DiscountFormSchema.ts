import { z } from "zod";

const integerPattern = /^\d+$/;

export const DiscountFormSchema = z
  .object({
    code: z.string().min(1, "Discount code is required"),
    description: z.string().optional().nullable().default(null),
    discountType: z
      .object({
        id: z.number(),
        name: z.string(),
      })
      .nullable()
      .refine((val) => val !== null, {
        message: "Discount Type is required",
      }),
    discountValue: z
      .string()
      .nullable()
      .refine(
        (val) => val !== "" && (val === null || integerPattern.test(val)),
        {
          message: "Discount Value is required",
        },
      ),
    maxUses: z.string().nullable().default(null),
    minPurchaseAmount: z.string().nullable().default(null),
    startDate: z.string().nullable().optional(),
    endDate: z.string().nullable().optional(),
    events: z
      .array(
        z.object({
          id: z.number(),
          name: z.string(),
        }),
      )
      .nullable(),

    isActive: z.boolean().nullable().default(true),
  })
  .refine(
    (data) =>
      !data.startDate ||
      !data.endDate ||
      new Date(data.endDate) >= new Date(data.startDate),
    {
      message: "End date cannot be earlier than start date",
      path: ["endDate"],
    },
  );

export type DiscountFormData = z.infer<typeof DiscountFormSchema>;
