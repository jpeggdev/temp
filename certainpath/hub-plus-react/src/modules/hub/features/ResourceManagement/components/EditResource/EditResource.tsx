import React, { useEffect, useState, useMemo, useCallback } from "react";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
  FormDescription,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import { Button } from "@/components/ui/button";
import { MultiSelect } from "@/components/MultiSelect/MultiSelect";
import { DatePicker } from "@/components/DatePicker/DatePicker";
import { SelectWithOptions } from "@/components/SelectWithOptions/SelectWithOptions";
import { ContentBlockEditor } from "@/modules/hub/features/ResourceManagement/components/ContentBlockEditor/ContentBlockEditor";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { useParams } from "react-router-dom";
import { useEditResource } from "@/modules/hub/features/ResourceManagement/hooks/useEditResource/useEditResource";
import { EntityMultiSelect } from "@/components/EntityMultiSelect/EntityMultiSelect";
import { getResourceCategories } from "@/modules/hub/features/ResourceCategoryManagement/api/getResourceCategories/getResourceCategoriesApi";
import { createResourceCategory } from "@/modules/hub/features/ResourceCategoryManagement/api/createResourceCategory/createResourceCategoryApi";
import { getResourceTags } from "@/modules/hub/features/ResourceTagManagement/api/getResourceTags/getResourceTagsApi";
import { createResourceTag } from "@/modules/hub/features/ResourceTagManagement/api/createResourceTag/createResourceTagApi";
import CreateUpdateResourceLoadingSkeleton from "@/modules/hub/features/ResourceManagement/components/CreateUpdateResourceLoadingSkeleton/CreateUpdateResourceLoadingSkeleton";
import { getResources } from "@/api/getResources/getResourcesApi";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import { validateResourceSlug } from "@/modules/hub/features/ResourceManagement/api/validateResourceSlug/validateResourceSlugApi";
import { Check, XCircle } from "lucide-react";
import FilePickerDialog from "@/modules/hub/features/FileManagement/components/FilePickerDialog/FilePickerDialog";
import { getPresignedUrls } from "@/modules/hub/features/FileManagement/api/getPresignedUrls/getPresignedUrlsApi";
import ThumbnailDisplay from "@/modules/hub/features/FileManagement/components/ThumbnailDisplay/ThumbnailDisplay";

interface RelatedResourceEntity {
  id: number;
  name: string;
  thumbnailUrl?: string | null;
  primaryIcon?: string | null;
  resourceType?: string | null;
  createdAt?: string;
}

export function EditResource() {
  const { uuid } = useParams<{ uuid: string }>();
  const {
    form,
    isLoading,
    requiresContentUrl,
    submitForm,
    handleCancel,
    trades,
    roles,
    resourceTypes,
    loadingGet,
    metadataLoading,
    resourceTitle,
    fetchedResource,
  } = useEditResource(uuid);

  const { control, handleSubmit, formState, setValue, getValues, watch } = form;
  const { isSubmitting } = formState;

  // File picker dialog state
  const [isFilePickerOpen, setIsFilePickerOpen] = useState(false);

  // Loading state for initial thumbnail load only
  const [loadingThumbnailUrl, setLoadingThumbnailUrl] = useState(false);

  // Track thumbnail filename
  const [thumbnailFileName, setThumbnailFileName] = useState<string>("");

  const manualBreadcrumbs = useMemo(() => {
    if (!uuid) return undefined;
    const titleOrFallback = resourceTitle || `Resource ${uuid}`;
    return [
      { path: "/admin/resources", label: "Resource List" },
      {
        path: `/admin/resources/${uuid}/edit`,
        label: `Editing Resource: ${titleOrFallback}`,
        clickable: false,
      },
    ];
  }, [uuid, resourceTitle]);

  // Initialize thumbnail with presigned URL when resource is loaded
  useEffect(() => {
    const loadThumbnail = async () => {
      if (
        fetchedResource &&
        fetchedResource.thumbnailFileUuid &&
        !form.getValues("thumbnail_url")
      ) {
        try {
          setLoadingThumbnailUrl(true);

          // Get presigned URL for the thumbnail
          const response = await getPresignedUrls({
            fileUuids: [fetchedResource.thumbnailFileUuid],
          });

          const presignedUrl =
            response.data.presignedUrls[fetchedResource.thumbnailFileUuid];

          if (presignedUrl) {
            // Update the form with the presigned URL
            setValue("thumbnail_url", presignedUrl, {
              shouldDirty: false,
              shouldTouch: false,
              shouldValidate: false,
            });

            // Set a placeholder filename since we don't have it in the response
            setThumbnailFileName("Resource thumbnail");
          }
        } catch (error) {
          console.error("Error loading thumbnail:", error);
        } finally {
          setLoadingThumbnailUrl(false);
        }
      }
    };

    loadThumbnail();
  }, [fetchedResource, setValue, form]);

  const titleValue = watch("title") || "";
  useEffect(() => {
    const autoSlug = titleValue
      .toLowerCase()
      .trim()
      .replace(/\s+/g, "-")
      .replace(/[^a-z0-9-]/g, "");
    setValue("slug", autoSlug, { shouldDirty: true });
  }, [titleValue, setValue]);

  const slugValue = watch("slug") || "";
  const debouncedSlug = useDebouncedValue(slugValue, 500);
  const [slugCheckStatus, setSlugCheckStatus] = useState<
    "idle" | "loading" | "valid" | "invalid"
  >("idle");
  const [slugCheckMessage, setSlugCheckMessage] = useState("");

  useEffect(() => {
    if (!debouncedSlug) {
      setSlugCheckStatus("idle");
      setSlugCheckMessage("");
      return;
    }
    setSlugCheckStatus("loading");
    validateResourceSlug({
      slug: debouncedSlug,
      resourceUuid: uuid || null,
    })
      .then((res) => {
        if (res.data.slugExists) {
          setSlugCheckStatus("invalid");
          setSlugCheckMessage(res.data.message || "Slug is already in use.");
        } else {
          setSlugCheckStatus("valid");
          setSlugCheckMessage("Slug is available!");
        }
      })
      .catch((err) => {
        console.error("Error validating slug:", err);
        setSlugCheckStatus("invalid");
        setSlugCheckMessage(
          "Error validating slug. Please try again or choose another.",
        );
      });
  }, [debouncedSlug, uuid]);

  // Handle thumbnail file selection
  const handleThumbnailSelected = useCallback(
    (
      files: Array<{
        fileUuid: string;
        fileUrl: string;
        presignedUrl: string;
        name: string;
      }>,
    ) => {
      if (files.length === 0) return;

      const file = files[0]; // Take only the first file for thumbnail

      // Set the thumbnail URL for display - now using the presigned URL directly from FilePickerDialog
      setValue("thumbnail_url", file.presignedUrl, {
        shouldDirty: true,
        shouldTouch: true,
        shouldValidate: false,
      });

      // Set the thumbnailFileUuid for the backend
      setValue("thumbnailFileUuid", file.fileUuid, {
        shouldDirty: true,
        shouldTouch: true,
        shouldValidate: false,
      });

      // Clear any old ID-based reference
      setValue("thumbnailFileId", null, {
        shouldDirty: true,
        shouldTouch: true,
        shouldValidate: false,
      });

      // Store the filename
      setThumbnailFileName(file.name);

      // Close the file picker dialog
      setIsFilePickerOpen(false);
    },
    [setValue],
  );

  // Handle thumbnail removal
  const handleRemoveThumbnail = useCallback(() => {
    setValue("thumbnail_url", "", {
      shouldDirty: true,
      shouldTouch: true,
      shouldValidate: false,
    });
    setValue("thumbnailFileUuid", null, {
      shouldDirty: true,
      shouldTouch: true,
      shouldValidate: false,
    });
    setValue("thumbnailFileId", null, {
      shouldDirty: true,
      shouldTouch: true,
      shouldValidate: false,
    });
    setThumbnailFileName("");
  }, [setValue]);

  if (loadingGet || metadataLoading) {
    return <CreateUpdateResourceLoadingSkeleton />;
  }

  return (
    <MainPageWrapper
      manualBreadcrumbs={manualBreadcrumbs}
      title="Edit Resource"
    >
      <Form {...form}>
        <form
          className="space-y-8 pb-24 bg-white"
          onSubmit={handleSubmit(submitForm)}
        >
          <div className="text-sm text-muted-foreground mb-2">
            Fields marked with an asterisk (*) are required.
          </div>

          <FormField
            control={control}
            name="title"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Title *</FormLabel>
                <FormControl>
                  <Input {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="slug"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Slug *</FormLabel>
                <FormControl>
                  <div className="relative">
                    <Input {...field} />
                    {slugValue && slugCheckStatus === "valid" && (
                      <Check className="absolute right-2 top-1/2 -translate-y-1/2 text-green-500" />
                    )}
                    {slugValue && slugCheckStatus === "invalid" && (
                      <XCircle className="absolute right-2 top-1/2 -translate-y-1/2 text-red-500" />
                    )}
                  </div>
                </FormControl>
                {slugCheckMessage && (
                  <p
                    className={`text-xs mt-1 ${
                      slugCheckStatus === "invalid"
                        ? "text-red-500"
                        : "text-green-500"
                    }`}
                  >
                    {slugCheckMessage}
                  </p>
                )}
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="tagline"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Tagline</FormLabel>
                <FormControl>
                  <Input {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="description"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Description *</FormLabel>
                <FormControl>
                  <Textarea {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="type"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Type *</FormLabel>
                <FormControl>
                  <SelectWithOptions
                    onValueChange={(val) => field.onChange(Number(val))}
                    options={resourceTypes.map((rt) => ({
                      label: rt.name.toUpperCase(),
                      value: String(rt.id),
                    }))}
                    value={field.value ? String(field.value) : ""}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          {requiresContentUrl && (
            <FormField
              control={control}
              name="content_url"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Content URL</FormLabel>
                  <FormControl>
                    <Input {...field} type="url" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          )}

          <FormField
            control={control}
            name="thumbnail_url"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Thumbnail</FormLabel>
                <FormControl>
                  <ThumbnailDisplay
                    loading={loadingThumbnailUrl}
                    onRemove={handleRemoveThumbnail}
                    onSelect={() => setIsFilePickerOpen(true)}
                    thumbnailFileName={thumbnailFileName}
                    thumbnailUrl={field.value}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="grid grid-cols-2 gap-6">
            <FormField
              control={control}
              name="publish_start_date"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Publish Start Date</FormLabel>
                  <FormControl>
                    <DatePicker onChange={field.onChange} value={field.value} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={control}
              name="publish_end_date"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Publish End Date</FormLabel>
                  <FormControl>
                    <DatePicker
                      minDate={getValues("publish_start_date") || null}
                      onChange={field.onChange}
                      value={field.value}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <FormField
            control={control}
            name="is_published"
            render={({ field }) => (
              <FormItem className="flex items-center justify-between rounded-lg border p-4">
                <div className="space-y-0.5">
                  <FormLabel className="text-base">Published</FormLabel>
                  <FormDescription>
                    Make this resource available to users
                  </FormDescription>
                </div>
                <FormControl>
                  <Switch
                    checked={field.value}
                    onCheckedChange={field.onChange}
                  />
                </FormControl>
              </FormItem>
            )}
          />

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <FormField
              control={control}
              name="categories"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Categories</FormLabel>
                  <FormControl>
                    <EntityMultiSelect
                      createEntity={async ({ name }) => {
                        const res = await createResourceCategory({ name });
                        return {
                          id: res.data.id ?? 0,
                          name: res.data.name ?? "",
                        };
                      }}
                      entityNamePlural="Categories"
                      entityNameSingular="Category"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await getResourceCategories({
                          name: searchTerm,
                          page,
                          pageSize,
                          sortBy: "name",
                          sortOrder: "ASC",
                        });
                        const { categories: catData } = response.data;
                        const totalCount =
                          response.meta?.totalCount ?? catData.length;
                        return {
                          data: catData.map((c) => ({
                            id: c.id,
                            name: c.name,
                          })),
                          totalCount,
                        };
                      }}
                      onChange={field.onChange}
                      value={field.value || []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={control}
              name="tags"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Tags</FormLabel>
                  <FormControl>
                    <EntityMultiSelect
                      createEntity={async ({ name }) => {
                        const res = await createResourceTag({ name });
                        return {
                          id: res.data.id ?? 0,
                          name: res.data.name ?? "",
                        };
                      }}
                      entityNamePlural="Tags"
                      entityNameSingular="Tag"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await getResourceTags({
                          name: searchTerm,
                          page,
                          pageSize,
                          sortBy: "name",
                          sortOrder: "ASC",
                        });
                        const { tags: tagData } = response.data;
                        const totalCount =
                          response.meta?.totalCount ?? tagData.length;
                        return {
                          data: tagData.map((t) => ({
                            id: t.id,
                            name: t.name,
                          })),
                          totalCount,
                        };
                      }}
                      onChange={field.onChange}
                      value={field.value || []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <FormField
              control={control}
              name="relatedResources"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Related Resources</FormLabel>
                  <FormControl>
                    <EntityMultiSelect<RelatedResourceEntity>
                      entityNamePlural="Resources"
                      entityNameSingular="Resource"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await getResources({
                          searchTerm,
                          page,
                          pageSize,
                          sortBy: "title",
                          sortOrder: "ASC",
                        });
                        const totalCount =
                          response.meta?.totalCount ?? response.data.length;

                        return {
                          data: response.data.map((r) => ({
                            id: r.id,
                            name: r.title,
                            thumbnailUrl: r.thumbnailUrl,
                            primaryIcon: r.primaryIcon,
                            resourceType: r.resourceType,
                            createdAt: r.createdAt,
                          })),
                          totalCount,
                        };
                      }}
                      onChange={field.onChange}
                      renderEntityRow={(ent, isSelected, toggle) => {
                        return (
                          <div
                            className={`
                  cursor-pointer flex items-center gap-4 py-4 px-2 
                  border-b last:border-0
                  ${isSelected ? "bg-blue-50" : ""}
                `}
                            key={ent.id}
                            onClick={() => toggle(ent)}
                          >
                            {ent.thumbnailUrl ? (
                              <img
                                alt={ent.name}
                                className="object-cover w-16 h-16 rounded flex-shrink-0"
                                src={ent.thumbnailUrl}
                              />
                            ) : (
                              <div className="w-16 h-16 rounded flex items-center justify-center bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                {ent.primaryIcon && (
                                  <span
                                    className="inline-block"
                                    dangerouslySetInnerHTML={{
                                      __html: ent.primaryIcon,
                                    }}
                                  />
                                )}
                              </div>
                            )}

                            <div className="flex-1">
                              <p className="font-medium hover:underline">
                                {ent.name}
                              </p>
                              <p className="text-xs text-gray-500 dark:text-gray-400">
                                {ent.resourceType} •{" "}
                                {ent.createdAt
                                  ? new Date(ent.createdAt).toLocaleDateString()
                                  : "No date"}
                              </p>
                            </div>
                            {isSelected && (
                              <Check className="h-5 w-5 text-primary flex-shrink-0" />
                            )}
                          </div>
                        );
                      }}
                      value={field.value || []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <FormField
              control={control}
              name="tradeIds"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Trades</FormLabel>
                  <FormControl>
                    <MultiSelect
                      onChange={field.onChange}
                      options={trades.map((tr) => ({
                        label: tr.name,
                        value: String(tr.id),
                      }))}
                      placeholder="Select trades..."
                      value={field.value?.map(String) ?? []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={control}
              name="roleIds"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Roles</FormLabel>
                  <FormControl>
                    <MultiSelect
                      onChange={field.onChange}
                      options={roles.map((r) => ({
                        label: r.name,
                        value: String(r.id),
                      }))}
                      placeholder="Select roles..."
                      value={field.value?.map(String) ?? []}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <FormField
            control={control}
            name="contentBlocks"
            render={({ field }) => (
              <FormItem className="space-y-4">
                <div className="flex flex-col space-y-2">
                  <FormLabel className="text-lg font-semibold">
                    Content Blocks
                  </FormLabel>
                  <FormDescription>
                    Add content blocks to structure your resource.
                  </FormDescription>
                </div>
                <FormControl>
                  <ContentBlockEditor
                    blocks={field.value || []}
                    onChange={field.onChange}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="sticky bottom-0 flex justify-end gap-4 px-4 py-4 border-t bg-white mt-8">
            <Button onClick={handleCancel} type="button" variant="outline">
              Cancel
            </Button>
            <Button type="submit">
              {isSubmitting || isLoading ? "Saving..." : "Save"}
            </Button>
          </div>
        </form>
      </Form>

      {/* File Picker Dialog for Thumbnail (single select) */}
      <FilePickerDialog
        allowedFileTypes={[
          "image/jpeg",
          "image/png",
          "image/gif",
          "image/webp",
        ]}
        isOpen={isFilePickerOpen}
        multiSelect={false}
        onClose={() => setIsFilePickerOpen(false)}
        onSelect={handleThumbnailSelected}
        title="Select Thumbnail"
      />
    </MainPageWrapper>
  );
}

export default EditResource;
