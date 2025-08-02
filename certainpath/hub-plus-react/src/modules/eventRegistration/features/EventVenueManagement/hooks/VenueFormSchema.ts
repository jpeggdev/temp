import { z } from "zod";

export const VenueFormSchema = z.object({
  name: z.string().min(1, "Venue name is required"),
  description: z.string().optional().nullable().default(null),
  address: z.string().min(1, "Address is required"),
  address2: z.string().optional().nullable().default(null),
  city: z.string().min(1, "City is required"),
  state: z.string().min(1, "State is required"),
  postalCode: z.string().min(1, "Postal Code is required"),
  country: z.string().min(1, "Country is required"),
  isActive: z.boolean().nullable().default(true),
});

export type VenueFormData = z.infer<typeof VenueFormSchema>;
