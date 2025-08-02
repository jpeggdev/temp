import { useEffect, useState, useCallback, useMemo } from "react";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { useToast } from "@/components/ui/use-toast";
import {
  ResourceFormData,
  resourceFormSchema,
} from "@/modules/hub/features/ResourceManagement/hooks/useCreateResource/types";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { getCreateUpdateResourceMetadataAction } from "@/modules/hub/features/ResourceManagement/slices/resourceManagementMetadataSlice";
import { createResourceAction } from "@/modules/hub/features/ResourceManagement/slices/createUpdateResourceSlice";
import { useNavigate } from "react-router-dom";
import { useNotification } from "@/context/NotificationContext";
import { slugify } from "@/utils/slugify";

export function useCreateResource() {
  const { toast } = useToast();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const [isLoading, setIsLoading] = useState(false);
  const { showNotification } = useNotification();
  const {
    resourceTags,
    resourceCategories,
    employeeRoles,
    trades,
    resourceTypes,
    loading: metadataLoading,
    error: metadataError,
  } = useAppSelector((state) => state.resourceManagementMetadataReducer);

  useEffect(() => {
    dispatch(getCreateUpdateResourceMetadataAction());
  }, [dispatch]);

  const defaultValues: ResourceFormData = {
    title: "",
    slug: "",
    tagline: "",
    description: "",
    type: null,
    content_url: "",
    thumbnail_url: "",
    thumbnailFileId: null,
    thumbnailFileUuid: null,
    publish_start_date: null,
    publish_end_date: null,
    is_published: false,
    tags: [],
    categories: [],
    tradeIds: [],
    roleIds: [],
    contentBlocks: [],
    relatedResources: [],
  };

  const form = useForm<ResourceFormData>({
    resolver: zodResolver(resourceFormSchema),
    defaultValues,
    mode: "onChange",
  });

  useEffect(() => {
    const defaultType = resourceTypes.find((rt) => rt.isDefault === true);
    if (defaultType) {
      const currentType = form.getValues("type");
      if (currentType === null) {
        form.setValue("type", defaultType.id, { shouldDirty: false });
      }
    }
  }, [resourceTypes, form]);

  const resourceTypeId = form.watch("type");

  const selectedResourceType = useMemo(() => {
    if (!resourceTypeId) return null;
    return resourceTypes.find((rt) => rt.id === resourceTypeId) || null;
  }, [resourceTypeId, resourceTypes]);

  const requiresContentUrl = selectedResourceType?.requiresContentUrl || false;

  const submitForm = useCallback(
    async (values: ResourceFormData) => {
      try {
        setIsLoading(true);
        const numericType = values.type ?? 0;

        const finalBlocks = (values.contentBlocks || []).map((b) => ({
          ...b,
          order_number: b.order_number ?? 0,
          // Include both fileId and fileUuid for backward compatibility
          fileId: b.fileId ?? null,
          fileUuid: b.fileUuid ?? null,
        }));

        const publishStart = values.publish_start_date
          ? values.publish_start_date.toISOString()
          : null;
        const publishEnd = values.publish_end_date
          ? values.publish_end_date.toISOString()
          : null;
        const tagIds = (values.tags || []).map((t) => t.id);
        const categoryIds = (values.categories || []).map((c) => c.id);
        const relatedResourceIds = (values.relatedResources || []).map(
          (r) => r.id,
        );
        const finalData = {
          title: values.title,
          slug: values.slug ? slugify(values.slug) : null,
          tagline: values.tagline || null,
          description: values.description,
          type: numericType,
          content_url: values.content_url || null,
          thumbnail_url: values.thumbnail_url || null,
          thumbnailFileId: values.thumbnailFileId ?? null,
          thumbnailFileUuid: values.thumbnailFileUuid ?? null,
          publish_start_date: publishStart,
          publish_end_date: publishEnd,
          is_published: values.is_published ?? false,
          tagIds,
          categoryIds,
          relatedResourceIds,
          tradeIds: values.tradeIds || [],
          roleIds: values.roleIds || [],
          contentBlocks: finalBlocks,
        };
        await dispatch(
          createResourceAction(finalData, () => {
            showNotification(
              "Success!",
              "Resource has been successfully created!",
              "success",
            );
            navigate(`/admin/resources`);
          }),
        );
      } catch (error) {
        console.error("Error saving resource:", error);
        if (error instanceof Error) {
          toast({
            title: "Error",
            description: error.message || "Failed to save",
            variant: "destructive",
          });
        } else {
          toast({
            title: "Error",
            description: "An unknown error occurred while saving.",
            variant: "destructive",
          });
        }
      } finally {
        setIsLoading(false);
      }
    },
    [dispatch, toast, navigate, showNotification],
  );

  const handleCancel = useCallback(() => {
    navigate(`/admin/resources`);
  }, [navigate]);

  return {
    form,
    isLoading,
    tags: resourceTags,
    trades,
    categories: resourceCategories,
    roles: employeeRoles,
    resourceTypes,
    metadataLoading,
    metadataError,
    resourceTypeId,
    selectedResourceType,
    requiresContentUrl,
    submitForm,
    handleCancel,
  };
}
