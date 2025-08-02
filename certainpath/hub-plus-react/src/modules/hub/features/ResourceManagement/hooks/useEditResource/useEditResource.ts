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
import {
  getResourceAction,
  updateResourceAction,
} from "@/modules/hub/features/ResourceManagement/slices/createUpdateResourceSlice";
import { useNavigate } from "react-router-dom";
import { useNotification } from "@/context/NotificationContext";
import { slugify } from "@/utils/slugify";

export function useEditResource(uuid: string | undefined) {
  const { toast } = useToast();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { showNotification } = useNotification();
  const [isLoading, setIsLoading] = useState(false);
  const {
    resourceTags,
    resourceCategories,
    employeeRoles,
    trades,
    resourceTypes,
    loading: metadataLoading,
    error: metadataError,
  } = useAppSelector((state) => state.resourceManagementMetadataReducer);
  const { fetchedResource, loadingUpdate, loadingGet } = useAppSelector(
    (state) => state.createUpdateResource,
  );

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
    if (fetchedResource) {
      form.reset({
        title: fetchedResource.title || "",
        slug: fetchedResource.slug || "",
        tagline: fetchedResource.tagline || "",
        description: fetchedResource.description || "",
        type: fetchedResource.typeId || 0,
        content_url: fetchedResource.contentUrl || "",
        thumbnail_url: fetchedResource.thumbnailUrl || "",
        thumbnailFileId: fetchedResource.thumbnailFileId || null,
        thumbnailFileUuid: fetchedResource.thumbnailFileUuid || null,
        publish_start_date: fetchedResource.publishStartDate
          ? new Date(fetchedResource.publishStartDate)
          : null,
        publish_end_date: fetchedResource.publishEndDate
          ? new Date(fetchedResource.publishEndDate)
          : null,
        is_published: fetchedResource.isPublished ?? false,
        tags:
          fetchedResource.tags?.map((t) => ({ id: t.id, name: t.name })) || [],
        categories:
          fetchedResource.categories?.map((c) => ({
            id: c.id,
            name: c.name,
          })) || [],
        tradeIds: fetchedResource.tradeIds?.map(String) || [],
        roleIds: fetchedResource.roleIds?.map(String) || [],
        contentBlocks:
          fetchedResource.contentBlocks?.map((block) => ({
            id: block.id,
            type: block.type,
            content: block.content,
            order_number: block.order_number,
            metadata: {},
            fileId: block.fileId || null,
            fileUuid: block.fileUuid || null,
            title: block.title || undefined,
            shortDescription: block.shortDescription || undefined,
          })) || [],
        relatedResources:
          fetchedResource.relatedResources?.map((rr) => ({
            id: rr.id,
            name: rr.title,
          })) || [],
      });
    }
  }, [fetchedResource, form]);

  const titleValue = form.watch("title") || "";
  useEffect(() => {
    const autoSlug = titleValue
      .toLowerCase()
      .trim()
      .replace(/\s+/g, "-")
      .replace(/[^a-z0-9-]/g, "");
    form.setValue("slug", autoSlug, { shouldDirty: true });
  }, [titleValue, form]);

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
        if (!fetchedResource) {
          throw new Error("No resource loaded to update");
        }
        const numericType = values.type ?? 0;
        const finalBlocks = (values.contentBlocks || []).map((b) => ({
          ...b,
          order_number: b.order_number ?? 0,
          // Include both fileId and fileUuid for backward compatibility
          fileId: b.fileId ?? null,
          fileUuid: b.fileUuid ?? null,
          title: b.title || undefined,
          shortDescription: b.shortDescription || undefined,
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
          slug: values.slug ? slugify(values.slug) : null,
          title: values.title,
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
          tradeIds: values.tradeIds || [],
          roleIds: values.roleIds || [],
          tagIds,
          categoryIds,
          relatedResourceIds,
          contentBlocks: finalBlocks,
        };
        const resourceId = fetchedResource.id;
        if (!resourceId) {
          throw new Error("No resource ID found to update");
        }
        await dispatch(
          updateResourceAction(resourceId, finalData, () => {
            showNotification(
              "Success!",
              "Resource has been successfully updated!",
              "success",
            );
            navigate(`/admin/resources`);
          }),
        );
      } catch (error) {
        console.error("Error updating resource:", error);
        if (error instanceof Error) {
          toast({
            title: "Error",
            description: error.message || "Failed to update",
            variant: "destructive",
          });
        } else {
          toast({
            title: "Error",
            description: "An unknown error occurred while updating.",
            variant: "destructive",
          });
        }
      } finally {
        setIsLoading(false);
      }
    },
    [dispatch, fetchedResource, toast, navigate, showNotification],
  );

  const handleCancel = useCallback(() => {
    navigate(`/admin/resources`);
  }, [navigate]);

  useEffect(() => {
    if (uuid) {
      dispatch(getResourceAction(uuid));
    }
  }, [uuid, dispatch]);

  return {
    form,
    isLoading: isLoading || loadingUpdate,
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
    loadingGet,
    resourceTitle: fetchedResource?.title,
    fetchedResource,
  };
}
