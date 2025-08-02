import { z } from "zod";

export const EmailCampaignFormSchema = z.object({
  name: z.string().min(1, "Campaign name is required"),
  description: z.string().nullable(),
  emailTemplate: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable()
    .refine((val) => val !== null, "Email template is required"),
  emailSubject: z.string().nullable(),
  event: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable()
    .refine((val) => val !== null, "Event is required"),
  session: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable()
    .refine((val) => val !== null, "Event session is required"),
  sendOption: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable()
    .refine((val) => val !== null, "Send option is required"),
});

export type EmailCampaignFormData = z.infer<typeof EmailCampaignFormSchema>;
