import { useCallback, useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { useNavigate } from "react-router-dom";

import { useAppDispatch, useAppSelector } from "@/app/hooks";
import {
  getEventAction,
  updateEventAction,
  fetchCreateUpdateEventMetadataAction,
  resetCreateUpdateEventState,
} from "../slices/createUpdateEventSlice";
import { useNotification } from "@/context/NotificationContext";
import { getPresignedUrls } from "@/modules/hub/features/FileManagement/api/getPresignedUrls/getPresignedUrlsApi";

const editEventFormSchema = z.object({
  eventName: z.string().min(1, "Event name is required"),
  eventCode: z.string().min(1, "Event code is required"),
  eventDescription: z.string().min(1, "Event description is required"),
  eventPrice: z.coerce.number().min(0, "Price must be ≥ 0"),
  isPublished: z.boolean().optional(),
  isVoucherEligible: z.boolean().optional(),

  thumbnailUrl: z.string().nullish(), // For UI display only
  thumbnailFileUuid: z.string().nullish(), // For backend storage

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

  fileUuids: z.array(z.string()).optional(),
});

export type EditEventFormData = z.infer<typeof editEventFormSchema>;

export function useEditEvent(uuid: string | undefined) {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { showNotification } = useNotification();
  const [eventFileNames, setEventFileNames] = useState<string[]>([]);

  const {
    loadingMetadata,
    metadataError,
    createUpdateEventMetadata,
    loadingGet,
    getError,
    fetchedEvent,
    loadingUpdate,
  } = useAppSelector((state) => state.createUpdateEvent);

  const trades = createUpdateEventMetadata?.trades ?? [];

  const form = useForm<EditEventFormData>({
    resolver: zodResolver(editEventFormSchema),
    defaultValues: {
      eventName: "",
      eventCode: "",
      eventDescription: "",
      eventPrice: 0,
      isPublished: false,
      isVoucherEligible: false,
      thumbnailUrl: null,
      thumbnailFileUuid: null,
      eventTypeId: null,
      eventCategoryId: null,
      tradeIds: [],
      roles: [],
      tags: [],
      fileUuids: [],
    },
    mode: "onChange",
  });

  const { reset } = form;

  useEffect(() => {
    dispatch(fetchCreateUpdateEventMetadataAction());
  }, [dispatch]);

  useEffect(() => {
    if (uuid) {
      dispatch(getEventAction(uuid));
    }

    return () => {
      dispatch(resetCreateUpdateEventState());
    };
  }, [uuid, dispatch]);

  useEffect(() => {
    if (fetchedEvent && fetchedEvent.id) {
      // Setup file names for display
      const fileNames =
        fetchedEvent.files?.map((f) => f.originalFileName || "Unknown file") ||
        [];
      setEventFileNames(fileNames);

      // Get presigned URL for thumbnail if it exists
      const loadThumbnailUrl = async () => {
        if (fetchedEvent.thumbnailFileUuid) {
          try {
            const response = await getPresignedUrls({
              fileUuids: [fetchedEvent.thumbnailFileUuid],
            });
            const thumbnailUrl =
              response.data.presignedUrls[fetchedEvent.thumbnailFileUuid];
            return thumbnailUrl || null;
          } catch (error) {
            console.error("Error loading thumbnail URL:", error);
            return null;
          }
        }
        return null;
      };

      // Extract file UUIDs from files array
      const fileUuids = fetchedEvent.files?.map((f) => f.uuid) || [];

      loadThumbnailUrl().then((thumbnailUrl) => {
        const defaultValues: EditEventFormData = {
          eventName: fetchedEvent.eventName,
          eventCode: fetchedEvent.eventCode,
          eventDescription: fetchedEvent.eventDescription,
          eventPrice: fetchedEvent.eventPrice ?? 0,
          isPublished: fetchedEvent.isPublished ?? false,
          isVoucherEligible: fetchedEvent.isVoucherEligible ?? false,
          thumbnailUrl: thumbnailUrl,
          thumbnailFileUuid: fetchedEvent.thumbnailFileUuid,
          eventTypeId: fetchedEvent.eventTypeId
            ? {
                id: fetchedEvent.eventTypeId,
                name: fetchedEvent.eventTypeName ?? "",
              }
            : null,
          eventCategoryId: fetchedEvent.eventCategoryId
            ? {
                id: fetchedEvent.eventCategoryId,
                name: fetchedEvent.eventCategoryName ?? "",
              }
            : null,
          tradeIds: fetchedEvent.trades?.map((t) => t.id) || [],
          roles: fetchedEvent.roles || [],
          tags: fetchedEvent.tags || [],
          fileUuids: fileUuids,
        };
        reset(defaultValues);
      });
    }
  }, [fetchedEvent, reset]);

  const submitForm = useCallback(
    async (values: EditEventFormData) => {
      try {
        if (!fetchedEvent || !fetchedEvent.id) {
          throw new Error("Cannot update: no event loaded.");
        }

        const finalTrades = (values.tradeIds || []).map(Number);
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
          thumbnailFileUuid: values.thumbnailFileUuid ?? undefined,
          eventTypeId: values.eventTypeId?.id ?? undefined,
          eventCategoryId: values.eventCategoryId?.id ?? undefined,
          roleIds: finalRoles.length ? finalRoles : undefined,
          tagIds: finalTags.length ? finalTags : undefined,
          tradeIds: finalTrades.length ? finalTrades : undefined,
          fileUuids: finalFileUuids.length ? finalFileUuids : undefined,
        };

        dispatch(
          updateEventAction(fetchedEvent.id, requestData, () => {
            showNotification(
              "Success!",
              "Event has been successfully updated!",
              "success",
            );
            navigate(`/event-registration/admin/events`);
          }),
        );
      } catch (error) {
        console.error("Error updating event:", error);
        if (error instanceof Error) {
          showNotification("Error", error.message, "error");
        } else {
          showNotification(
            "Error",
            "An unknown error occurred while updating.",
            "error",
          );
        }
      }
    },
    [dispatch, navigate, showNotification, fetchedEvent],
  );

  const handleCancel = useCallback(() => {
    navigate(`/event-registration/admin/events`);
  }, [navigate]);

  return {
    form,
    loadingMetadata,
    metadataError,
    loadingGet,
    getError,
    fetchedEvent,
    trades,
    loadingUpdate,
    submitForm,
    handleCancel,
    eventFileNames,
  };
}
