import { useCallback, useEffect } from "react";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { useNavigate } from "react-router-dom";
import {
  createEventAction,
  fetchCreateUpdateEventMetadataAction,
} from "../slices/createUpdateEventSlice";
import { useNotification } from "@/context/NotificationContext";

export const createEventFormSchema = z.object({
  eventName: z.string().min(1, "Event name is required"),
  eventCode: z.string().min(1, "Event code is required"),
  eventDescription: z.string().min(1, "Event description is required"),
  eventPrice: z.coerce.number().min(0, "Price must be ≥ 0"),
  isPublished: z.boolean().optional(),
  isVoucherEligible: z.boolean().optional(),

  thumbnailUrl: z.string().nullish(),
  // Support both ID and UUID approaches
  thumbnailFileId: z.number().nullish(),
  thumbnailFileUuid: z.string().nullish(),

  eventTypeId: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable(),

  eventCategoryId: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable()
    .optional(),

  tradeIds: z.array(z.number()).optional(),

  roles: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
      }),
    )
    .optional(),

  tags: z
    .array(
      z.object({
        id: z.number(),
        name: z.string(),
      }),
    )
    .optional(),

  // Support both ID and UUID approaches for file references
  fileIds: z.array(z.number()).optional(),
  fileUuids: z.array(z.string()).optional(),
});

export type CreateEventFormData = z.infer<typeof createEventFormSchema>;

export function useCreateEvent() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { showNotification } = useNotification();

  const {
    loadingMetadata,
    metadataError,
    loadingCreate,
    createUpdateEventMetadata,
  } = useAppSelector((state) => state.createUpdateEvent);

  const trades = createUpdateEventMetadata?.trades ?? [];

  const form = useForm<CreateEventFormData>({
    resolver: zodResolver(createEventFormSchema),
    defaultValues: {
      eventName: "",
      eventCode: "",
      eventDescription: "",
      eventPrice: 0,
      isPublished: false,
      isVoucherEligible: false,
      thumbnailUrl: null,
      thumbnailFileId: null,
      thumbnailFileUuid: null,

      eventTypeId: null,
      eventCategoryId: null,
      tradeIds: [],
      roles: [],
      tags: [],
      fileIds: [],
      fileUuids: [],
    },
  });

  useEffect(() => {
    dispatch(fetchCreateUpdateEventMetadataAction());
  }, [dispatch]);

  const submitForm = useCallback(
    async (values: CreateEventFormData) => {
      try {
        const finalTrades = (values.tradeIds || []).map(Number);
        const finalFileIds = (values.fileIds || []).map(Number);
        const finalFileUuids = values.fileUuids || [];
        const finalRoles = (values.roles || []).map((r) => r.id);
        const finalTags = (values.tags || []).map((t) => t.id);

        const requestData = {
          eventCode: values.eventCode,
          eventName: values.eventName,
          eventDescription: values.eventDescription,
          eventPrice: Number(values.eventPrice),
          isPublished: values.isPublished ?? false,
          isVoucherEligible: values.isVoucherEligible ?? false,
          thumbnailUrl: values.thumbnailUrl ?? undefined,
          thumbnailFileId: values.thumbnailFileId ?? undefined,
          thumbnailFileUuid: values.thumbnailFileUuid ?? undefined,
          eventTypeId: values.eventTypeId?.id ?? undefined,
          eventCategoryId: values.eventCategoryId?.id ?? undefined,
          fileIds: finalFileIds.length ? finalFileIds : undefined,
          fileUuids: finalFileUuids.length ? finalFileUuids : undefined,

          roleIds: finalRoles.length ? finalRoles : undefined,
          tagIds: finalTags.length ? finalTags : undefined,
          tradeIds: finalTrades.length ? finalTrades : undefined,
        };

        dispatch(
          createEventAction(requestData, () => {
            showNotification(
              "Success!",
              "Event has been successfully created!",
              "success",
            );
            navigate(`/event-registration/admin/events`);
          }),
        );
      } catch (error) {
        console.error("Error saving resource:", error);
        if (error instanceof Error) {
          showNotification("Error", error.message, "error");
        } else {
          showNotification(
            "Error",
            "An unknown error occurred while saving.",
            "error",
          );
        }
      }
    },
    [dispatch, navigate, showNotification],
  );

  const handleCancel = useCallback(() => {
    navigate(`/event-registration/admin/events`);
  }, [navigate]);

  return {
    form,
    loadingMetadata,
    metadataError,
    trades,
    loadingCreate,
    submitForm,
    handleCancel,
  };
}
