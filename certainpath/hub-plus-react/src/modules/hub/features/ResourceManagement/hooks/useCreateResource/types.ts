import { z } from "zod";

export const ResourceTypes = {
  DOCUMENT: "document",
  VIDEO: "video",
  PODCAST: "podcast",
} as const;

export const resourceFormSchema = z.object({
  title: z.string().min(1, "Title is required"),
  slug: z.string().min(1, "Slug is required"),
  tagline: z.string().optional(),
  description: z.string().min(1, "Description is required"),
  type: z.coerce.number().nullable().optional(),
  content_url: z.string().optional(),
  thumbnail_url: z.string().optional(),
  thumbnailFileId: z.number().nullable().optional(),
  thumbnailFileUuid: z.string().nullable().optional(),
  publish_start_date: z.coerce.date().nullable().optional(),
  publish_end_date: z.coerce.date().nullable().optional(),
  is_published: z.boolean().optional(),
  tags: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
      }),
    )
    .optional(),
  categories: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
      }),
    )
    .optional(),
  tradeIds: z.array(z.string()).optional(),
  roleIds: z.array(z.string()).optional(),

  contentBlocks: z
    .array(
      z.object({
        id: z.string().optional(),
        type: z.enum(["text", "image", "file", "vimeo", "youtube"]),
        content: z.string(),
        order_number: z.number().optional(),
        metadata: z.record(z.any()).optional(),
        fileId: z.number().nullable().optional(),
        fileUuid: z.string().nullable().optional(),
        title: z.string().optional(),
        shortDescription: z.string().optional(),
      }),
    )
    .optional(),

  relatedResources: z
    .array(z.object({ id: z.number(), name: z.string() }))
    .optional(),
});

export type ResourceFormData = z.infer<typeof resourceFormSchema>;
