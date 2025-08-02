import { z } from "zod";

export const EmailTemplateFormSchema = z.object({
  templateName: z.string().min(1, "Template name is required"),
  emailSubject: z.string().min(1, "Email subject is required"),
  emailContent: z.string().min(1, "Email content is required"),
  categories: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
        displayedName: z.string().optional(),
        color: z
          .object({
            id: z.number(),
            value: z.string(),
          })
          .optional(),
      }),
    )
    .min(1, "At least one category must be selected"),
});

export type EmailTemplateFormData = z.infer<typeof EmailTemplateFormSchema>;
