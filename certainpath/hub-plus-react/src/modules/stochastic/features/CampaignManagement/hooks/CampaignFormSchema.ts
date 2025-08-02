import { z } from "zod";

export const CampaignFormSchema = z.object({
  locations: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
        postalCodes: z.array(z.number()).optional(),
        isActive: z.boolean().optional(),
      }),
    )
    .optional(),
});

export type CampaignFormData = z.infer<typeof CampaignFormSchema>;
