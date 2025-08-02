import { z } from "zod";

export const VoucherFormSchema = z
  .object({
    name: z.string().min(1, "Voucher name is required"),
    company: z
      .object({
        id: z.number(),
        name: z.string(),
        companyIdentifier: z.string(),
      })
      .nullable()
      .refine((val) => val !== null, {
        message: "Company is required",
      }),
    description: z.string().optional().nullable().default(null),
    totalSeats: z.number(),
    startDate: z.string().optional().nullable(),
    endDate: z.string().optional().nullable(),
    isActive: z.boolean(),
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

export type VoucherFormData = z.infer<typeof VoucherFormSchema>;
