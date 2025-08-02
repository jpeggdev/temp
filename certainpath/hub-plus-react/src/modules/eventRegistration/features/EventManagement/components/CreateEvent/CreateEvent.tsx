import React, { useCallback, useMemo, useState, useEffect } from "react";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import { Button } from "@/components/ui/button";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { MultiSelect } from "@/components/MultiSelect/MultiSelect";
import type { CleaveOptions } from "cleave.js/options";
import {
  CreateEventFormData,
  useCreateEvent,
} from "@/modules/eventRegistration/features/EventManagement/hooks/useCreateEvent";
import { EntitySingleSelect } from "@/components/EntitySingleSelect/EntitySingleSelect";
import { EntityMultiSelect } from "@/components/EntityMultiSelect/EntityMultiSelect";
import { createEventType } from "@/modules/eventRegistration/features/EventTypeManagement/api/createEventType/createEventTypeApi";
import { fetchEventTypes } from "@/modules/eventRegistration/features/EventTypeManagement/api/fetchEventTypes/fetchEventTypesApi";
import { fetchEventCategories } from "@/api/fetchEventCategories/fetchEventCategoriesApi";
import { createEmployeeRole } from "@/modules/hub/features/EmployeeRoleManagement/api/createEmployeeRole/createEmployeeRoleApi";
import { getEmployeeRoles } from "@/modules/hub/features/EmployeeRoleManagement/api/getEmployeeRoles/getEmployeeRolesApi";
import { createEventTag } from "@/modules/eventRegistration/features/EventTagManagement/api/createEventTag/createEventTagApi";
import { fetchEventTags } from "@/modules/eventRegistration/features/EventTagManagement/api/fetchEventTags/fetchEventTagsApi";
import CreateUpdateEventLoadingSkeleton from "@/modules/eventRegistration/features/EventManagement/components/CreateUpdateEventLoadingSkeleton/CreateUpdateEventLoadingSkeleton";
import CustomCleaveInput from "@/components/CustomCleaveInput/CustomCleaveInput";
import { validateEventCode } from "@/modules/eventRegistration/features/EventManagement/api/validateEventCode/validateEventCodeApi";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import { Check, XCircle } from "lucide-react";
import { createEventCategory } from "@/api/createEventCategory/createEventCategoryApi";
import FilePickerDialog from "@/modules/hub/features/FileManagement/components/FilePickerDialog/FilePickerDialog";
import { useNotification } from "@/context/NotificationContext";
import FileCollectionDisplay, {
  FileItem,
} from "@/modules/hub/features/FileManagement/components/FileCollectionDisplay/FileCollectionDisplay";
import ThumbnailDisplay from "@/modules/hub/features/FileManagement/components/ThumbnailDisplay/ThumbnailDisplay";

export default function CreateEvent() {
  const {
    form,
    loadingMetadata,
    metadataError,
    trades,
    loadingCreate,
    submitForm,
    handleCancel,
  } = useCreateEvent();

  const { control, handleSubmit, setValue, watch } = form;
  const eventCodeValue = watch("eventCode") || "";
  const debouncedEventCode = useDebouncedValue(eventCodeValue, 500);

  // File picker dialog states
  const [isFilePickerOpen, setIsFilePickerOpen] = useState(false);
  const [isEventFilesPickerOpen, setIsEventFilesPickerOpen] = useState(false);

  // Track event files with full details including presigned URLs
  const [eventFiles, setEventFiles] = useState<FileItem[]>([]);

  // Track thumbnail filename
  const [thumbnailFileName, setThumbnailFileName] = useState<string>("");

  const [codeCheckStatus, setCodeCheckStatus] = useState<
    "idle" | "loading" | "valid" | "invalid"
  >("idle");
  const [codeCheckMessage, setCodeCheckMessage] = useState("");

  useEffect(() => {
    if (!debouncedEventCode) {
      setCodeCheckStatus("idle");
      setCodeCheckMessage("");
      return;
    }

    setCodeCheckStatus("loading");
    validateEventCode({ eventCode: debouncedEventCode })
      .then((res) => {
        if (res.data.codeExists) {
          setCodeCheckStatus("invalid");
          setCodeCheckMessage(
            res.data.message || "Event code is already in use.",
          );
        } else {
          setCodeCheckStatus("valid");
          setCodeCheckMessage("Event code is available!");
        }
      })
      .catch((err) => {
        console.error("Error validating code:", err);
        setCodeCheckStatus("invalid");
        setCodeCheckMessage(
          "Error validating code. Please try again or choose another.",
        );
      });
  }, [debouncedEventCode]);

  const onSubmit = useCallback(
    (values: CreateEventFormData) => {
      const fileUuids = form.getValues("fileUuids") || [];
      submitForm({ ...values, fileUuids });
    },
    [form, submitForm],
  );

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
      setValue("thumbnailUrl", file.presignedUrl, {
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
    setValue("thumbnailUrl", "", {
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

  // Handle event files selection (multi-select)
  const handleEventFilesSelected = useCallback(
    (
      files: Array<{
        fileUuid: string;
        fileUrl: string;
        presignedUrl: string;
        name: string;
      }>,
    ) => {
      if (files.length === 0) return;

      // Create file objects with presigned URLs
      const filesWithUrls: FileItem[] = files.map((file) => ({
        uuid: file.fileUuid,
        name: file.name,
        url: file.presignedUrl, // Use the presigned URL directly
        // You might want to get mimeType from the file selection if available
      }));

      // Get all the current fileUuids
      const currentFileUuids = form.getValues("fileUuids") || [];

      // Add the new file UUIDs
      const newFileUuids = [
        ...currentFileUuids,
        ...files.map((file) => file.fileUuid),
      ];

      // Update the form value
      setValue("fileUuids", newFileUuids, {
        shouldDirty: true,
        shouldTouch: true,
        shouldValidate: false,
      });

      // Store the file details for display
      setEventFiles((prev) => [...prev, ...filesWithUrls]);

      // Close the file picker dialog
      setIsEventFilesPickerOpen(false);
    },
    [form, setValue],
  );

  // Remove a single file from the selection
  const handleRemoveFile = useCallback(
    (fileUuid: string) => {
      // Update eventFiles state
      setEventFiles((prev) => prev.filter((file) => file.uuid !== fileUuid));

      // Update form fileUuids
      const currentFileUuids = form.getValues("fileUuids") || [];
      const updatedFileUuids = currentFileUuids.filter(
        (uuid) => uuid !== fileUuid,
      );
      setValue("fileUuids", updatedFileUuids, {
        shouldDirty: true,
        shouldTouch: true,
        shouldValidate: false,
      });
    },
    [form, setValue],
  );

  // Clear all files
  const handleClearAllFiles = useCallback(() => {
    setValue("fileUuids", [], {
      shouldDirty: true,
      shouldTouch: true,
      shouldValidate: false,
    });
    setEventFiles([]);
  }, [setValue]);

  const cleavePriceOptions: CleaveOptions = useMemo(() => {
    return {
      numeral: true,
      numeralDecimalScale: 2,
      numeralThousandsGroupStyle: "thousand",
      prefix: "$",
      rawValueTrimPrefix: true,
      numeralPositiveOnly: true,
    };
  }, []);

  if (loadingMetadata) {
    return <CreateUpdateEventLoadingSkeleton />;
  }

  if (metadataError) {
    return (
      <MainPageWrapper title="Create Event">
        <p className="text-red-500">Error loading metadata: {metadataError}</p>
      </MainPageWrapper>
    );
  }

  return (
    <MainPageWrapper title="Create Event">
      <Form {...form}>
        <form
          className="space-y-8 pb-24 bg-white"
          onSubmit={handleSubmit(onSubmit)}
        >
          <div className="text-sm text-muted-foreground mb-2">
            Fields marked with an asterisk (*) are required.
          </div>

          <FormField
            control={control}
            name="eventName"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Event Name *</FormLabel>
                <FormControl>
                  <input
                    {...field}
                    className="border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-600"
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="eventCode"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Event Code *</FormLabel>
                <FormControl>
                  <div className="relative">
                    <input
                      {...field}
                      className="border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-600"
                    />
                    {eventCodeValue && codeCheckStatus === "valid" && (
                      <Check className="absolute right-2 top-1/2 -translate-y-1/2 text-green-500" />
                    )}
                    {eventCodeValue && codeCheckStatus === "invalid" && (
                      <XCircle className="absolute right-2 top-1/2 -translate-y-1/2 text-red-500" />
                    )}
                  </div>
                </FormControl>
                {codeCheckMessage && (
                  <p
                    className={`text-xs mt-1 ${
                      codeCheckStatus === "invalid"
                        ? "text-red-500"
                        : "text-green-500"
                    }`}
                  >
                    {codeCheckMessage}
                  </p>
                )}
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="eventDescription"
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
            name="eventPrice"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Price *</FormLabel>
                <FormControl>
                  <CustomCleaveInput
                    className="border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-600"
                    inputMode="decimal"
                    onChange={(rawValue: string) => {
                      field.onChange(rawValue);
                    }}
                    onPaste={(e) => e.preventDefault()}
                    options={cleavePriceOptions}
                    value={String(field.value ?? "")}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="isPublished"
            render={({ field }) => (
              <FormItem className="flex items-center justify-between rounded-lg border p-4">
                <div className="space-y-0.5">
                  <FormLabel className="text-base">Published</FormLabel>
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

          <FormField
            control={control}
            name="isVoucherEligible"
            render={({ field }) => (
              <FormItem className="flex items-center justify-between rounded-lg border p-4">
                <div className="space-y-0.5">
                  <FormLabel className="text-base">Voucher Eligible</FormLabel>
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

          <FormField
            control={control}
            name="thumbnailUrl"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Thumbnail</FormLabel>
                <FormControl>
                  <ThumbnailDisplay
                    loading={false} // No longer needed since loading happens in the dialog
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

          <FormField
            control={control}
            name="eventTypeId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Event Type</FormLabel>
                <FormControl>
                  <EntitySingleSelect
                    createEntity={async ({ name }) => {
                      const res = await createEventType({
                        name,
                        isActive: true,
                      });
                      const created = res.data;
                      return {
                        id: created.id ?? 0,
                        name: created.name ?? "",
                      };
                    }}
                    entityNamePlural="Event Types"
                    entityNameSingular="Event Type"
                    fetchEntities={async ({ searchTerm, page, pageSize }) => {
                      const sortBy = "name";
                      const sortOrder = "ASC";
                      const isActive = true;
                      const result = await fetchEventTypes({
                        name: searchTerm,
                        isActive,
                        page,
                        pageSize,
                        sortBy,
                        sortOrder,
                      });
                      const { eventTypes, totalCount } = result.data;
                      return {
                        data: eventTypes.map((et) => ({
                          id: et.id,
                          name: et.name,
                        })),
                        totalCount: totalCount,
                      };
                    }}
                    onChange={field.onChange}
                    value={field.value || null}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="eventCategoryId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Event Category</FormLabel>
                <FormControl>
                  <EntitySingleSelect
                    createEntity={async ({ name }) => {
                      const res = await createEventCategory({
                        name,
                        description: null,
                        isActive: true,
                      });
                      const { eventCategory } = res.data;
                      return {
                        id: eventCategory.id,
                        name: eventCategory.name,
                      };
                    }}
                    entityNamePlural="Event Categories"
                    entityNameSingular="Event Category"
                    fetchEntities={async ({ searchTerm, page, pageSize }) => {
                      const sortBy = "name";
                      const sortOrder = "ASC";
                      const isActive = true;
                      const result = await fetchEventCategories({
                        name: searchTerm,
                        isActive,
                        page,
                        pageSize,
                        sortBy,
                        sortOrder,
                      });
                      const { data, meta } = result;
                      return {
                        data: data.map((cat) => ({
                          id: cat.id,
                          name: cat.name,
                        })),
                        totalCount: meta.totalCount,
                      };
                    }}
                    onChange={field.onChange}
                    value={field.value || null}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="tradeIds"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Trades</FormLabel>
                <FormControl>
                  <MultiSelect
                    onChange={(vals) => field.onChange(vals.map(Number))}
                    options={trades.map((t) => ({
                      label: t.name,
                      value: String(t.id),
                    }))}
                    placeholder="Select trades..."
                    value={field.value?.map(String) ?? []}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <FormField
              control={control}
              name="roles"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Roles</FormLabel>
                  <FormControl>
                    <EntityMultiSelect
                      createEntity={async ({ name }) => {
                        const res = await createEmployeeRole({ name });
                        return {
                          id: res.data.id ?? 0,
                          name: res.data.name ?? "",
                        };
                      }}
                      entityNamePlural="Employee Roles"
                      entityNameSingular="Employee Role"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await getEmployeeRoles({
                          name: searchTerm,
                          page,
                          pageSize,
                          sortBy: "name",
                          sortOrder: "ASC",
                        });
                        const { roles } = response.data;
                        const totalCount =
                          response.data.totalCount ?? roles.length;
                        return {
                          data: roles.map((r) => ({
                            id: r.id,
                            name: r.name,
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
                        const res = await createEventTag({ name });
                        return {
                          id: res.data.id ?? 0,
                          name: res.data.name ?? "",
                        };
                      }}
                      entityNamePlural="Event Tags"
                      entityNameSingular="Event Tag"
                      fetchEntities={async ({ searchTerm, page, pageSize }) => {
                        const response = await fetchEventTags({
                          name: searchTerm,
                          page,
                          pageSize,
                          sortBy: "name",
                          sortOrder: "ASC",
                        });
                        const { tags, totalCount } = response.data;
                        return {
                          data: tags.map((t) => ({
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

          <FormField
            control={control}
            name="fileUuids"
            render={() => (
              <FormItem>
                <FormLabel>Event Files</FormLabel>
                <FormControl>
                  <FileCollectionDisplay
                    files={eventFiles}
                    loading={false} // No longer needed since loading happens in the dialog
                    onClearAll={handleClearAllFiles}
                    onRemoveFile={handleRemoveFile}
                    onSelectFiles={() => setIsEventFilesPickerOpen(true)}
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
              {loadingCreate ? "Saving..." : "Save"}
            </Button>
          </div>
        </form>
      </Form>

      {/* File Picker Dialog for Thumbnail (single select) */}
      <FilePickerDialog
        allowedFileTypes={["image/jpeg", "image/png", "image/gif"]}
        isOpen={isFilePickerOpen}
        multiSelect={false}
        onClose={() => setIsFilePickerOpen(false)}
        onSelect={handleThumbnailSelected}
        title="Select Thumbnail"
      />

      {/* File Picker Dialog for Event Files (multi-select) */}
      <FilePickerDialog
        isOpen={isEventFilesPickerOpen}
        multiSelect={true}
        onClose={() => setIsEventFilesPickerOpen(false)}
        onSelect={handleEventFilesSelected}
        title="Select Event Files"
      />
    </MainPageWrapper>
  );
}
