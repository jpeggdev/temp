import React from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";

import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import CustomCleaveInput from "@/components/CustomCleaveInput/CustomCleaveInput";
import { Input } from "@/components/ui/input";
import { DatePicker } from "@/components/DatePicker/DatePicker";

import { EntitySingleSelect } from "@/components/EntitySingleSelect/EntitySingleSelect";
import { searchEventInstructors } from "@/modules/eventRegistration/features/EventInstructorManagement/api/searchEventInstructors/searchEventInstructorsApi";
import { getEventVenueLookupApi } from "@/api/getEventVenueLookup/getEventVenueLookupApi";
import { GetEventVenueLookupRequest } from "@/api/getEventVenueLookup/types";
import { fetchEventSessionMetadata } from "@/modules/eventRegistration/features/EventSessionManagement/api/fetchEventSessionMetadata/fetchEventSessionMetadataApi";

const eventSessionFormSchema = z.object({
  name: z.string().min(1, "Session name is required"),
  startDate: z.string().min(1, "Start date is required"),
  endDate: z.string().min(1, "End date is required"),
  maxEnrollments: z.coerce
    .number()
    .min(0, "Max enrollments must be ≥ 0")
    .optional(),
  virtualLink: z.string().optional(),
  notes: z.string().optional(),
  isPublished: z.boolean().optional(),
  instructorId: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable(),
  isVirtualOnly: z.boolean().optional(),
  venueId: z
    .object({
      id: z.number(),
      name: z.string(),
    })
    .nullable(),
  timezoneId: z
    .number({
      required_error: "Timezone is required",
      invalid_type_error: "Timezone must be a number",
    })
    .min(1, "Please select a valid timezone"),
});

export type EventSessionFormValues = z.infer<typeof eventSessionFormSchema>;

interface EventSessionFormProps {
  initialData: Partial<EventSessionFormValues>;
  loading?: boolean;
  onSubmit: (values: EventSessionFormValues) => void;
}

function EventSessionForm({
  initialData,
  loading = false,
  onSubmit,
}: EventSessionFormProps) {
  const formMethods = useForm<EventSessionFormValues>({
    resolver: zodResolver(eventSessionFormSchema),
    defaultValues: {
      name: "",
      startDate: "",
      endDate: "",
      maxEnrollments: 0,
      virtualLink: "",
      notes: "",
      isPublished: false,
      instructorId: null,
      isVirtualOnly: false,
      venueId: null,
      timezoneId: undefined as unknown as number,
      ...initialData,
    },
  });

  const {
    handleSubmit,
    control,
    formState: { isSubmitting },
    watch,
    setValue,
  } = formMethods;

  const [timezones, setTimezones] = React.useState<
    Array<{ label: string; value: string; id: number }>
  >([]);
  const [isTimezonesLoading, setIsTimezonesLoading] = React.useState(false);

  React.useEffect(() => {
    setIsTimezonesLoading(true);
    fetchEventSessionMetadata()
      .then((res) => {
        // res.data.timezones: Array<{ id: number; name: string; identifier: string }>
        const tzData = res.data.timezones.map((tz) => ({
          label: tz.name,
          value: tz.identifier,
          id: tz.id,
        }));
        setTimezones(tzData);
      })
      .finally(() => {
        setIsTimezonesLoading(false);
      });
  }, []);

  const maxEnrollmentsValue = watch("maxEnrollments") ?? 0;
  const isVirtualOnlyValue = watch("isVirtualOnly") ?? false;
  const selectedTimezoneId = watch("timezoneId");

  const selectedTimezoneObj = React.useMemo(() => {
    if (selectedTimezoneId == null) return undefined;
    return timezones.find((t) => t.id === selectedTimezoneId);
  }, [selectedTimezoneId, timezones]);

  const handleDateChange =
    (fieldName: "startDate" | "endDate") => (selectedDate: Date | null) => {
      if (!selectedDate) {
        setValue(fieldName, "");
        return;
      }
      setValue(fieldName, selectedDate.toISOString());

      if (fieldName === "startDate") {
        const endVal = watch("endDate");
        if (!endVal) {
          setValue("endDate", selectedDate.toISOString());
        }
      }
    };

  function parseIsoString(value: string | undefined): Date | null {
    if (!value) return null;
    try {
      return new Date(value);
    } catch {
      return null;
    }
  }

  const handleFormSubmit = (data: EventSessionFormValues) => {
    onSubmit({ ...data });
  };

  return (
    <Form {...formMethods}>
      <form className="space-y-4" onSubmit={handleSubmit(handleFormSubmit)}>
        <FormField
          control={control}
          name="name"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Session Name</FormLabel>
              <FormControl>
                <Input {...field} placeholder="Enter session name" />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="timezoneId"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Timezone</FormLabel>
              <FormControl>
                <select
                  className="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm"
                  disabled={isTimezonesLoading}
                  onChange={(e) => {
                    const numericVal = parseInt(e.target.value, 10);
                    field.onChange(isNaN(numericVal) ? undefined : numericVal);
                  }}
                  value={field.value ?? ""}
                >
                  {isTimezonesLoading ? (
                    <option value="">Loading timezones...</option>
                  ) : (
                    <>
                      <option value="">-- Select a time zone --</option>
                      {timezones.map((tz) => (
                        <option key={tz.id} value={tz.id}>
                          {tz.label}
                        </option>
                      ))}
                    </>
                  )}
                </select>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="startDate"
          render={({ field }) => {
            const dateValue = parseIsoString(field.value);
            return (
              <FormItem>
                <FormLabel>Start Date/Time</FormLabel>
                <FormControl>
                  <DatePicker
                    onChange={handleDateChange("startDate")}
                    placeholder="Pick a start date/time"
                    showTimeSelect
                    timeZone={selectedTimezoneObj?.value}
                    value={dateValue}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            );
          }}
        />

        <FormField
          control={control}
          name="endDate"
          render={({ field }) => {
            const dateValue = parseIsoString(field.value);
            return (
              <FormItem>
                <FormLabel>End Date/Time</FormLabel>
                <FormControl>
                  <DatePicker
                    onChange={handleDateChange("endDate")}
                    placeholder="Pick an end date/time"
                    showTimeSelect
                    timeZone={selectedTimezoneObj?.value}
                    value={dateValue}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            );
          }}
        />

        <FormField
          control={control}
          name="isVirtualOnly"
          render={({ field }) => (
            <FormItem className="flex items-center justify-between rounded-lg border p-4">
              <div className="space-y-0.5">
                <FormLabel className="text-base">Virtual-Only</FormLabel>
              </div>
              <FormControl>
                <Switch
                  checked={Boolean(field.value)}
                  onCheckedChange={field.onChange}
                />
              </FormControl>
            </FormItem>
          )}
        />

        {!isVirtualOnlyValue && (
          <FormField
            control={control}
            name="venueId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Venue</FormLabel>
                <FormControl>
                  <EntitySingleSelect
                    entityNamePlural="Venues"
                    entityNameSingular="Venue"
                    fetchEntities={async ({ searchTerm, page, pageSize }) => {
                      const sortBy = "name";
                      const sortOrder = "asc";
                      const result = await getEventVenueLookupApi({
                        isActive: true,
                        searchTerm,
                        page,
                        pageSize,
                        sortBy,
                        sortOrder,
                      } as GetEventVenueLookupRequest);

                      return {
                        data: result.data.map((venue) => ({
                          id: venue.id,
                          name: venue.name,
                        })),
                        totalCount: result.meta.totalCount,
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
        )}

        <FormField
          control={control}
          name="instructorId"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Instructor</FormLabel>
              <FormControl>
                <EntitySingleSelect
                  entityNamePlural="Instructors"
                  entityNameSingular="Instructor"
                  fetchEntities={async ({ searchTerm, page, pageSize }) => {
                    const sortBy = "name";
                    const sortOrder = "ASC";
                    const result = await searchEventInstructors({
                      searchTerm,
                      page,
                      pageSize,
                      sortBy,
                      sortOrder,
                    });
                    const { instructors, totalCount } = result.data;

                    return {
                      data: instructors.map((inst) => ({
                        id: inst.id,
                        name: inst.name,
                      })),
                      totalCount,
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
          name="maxEnrollments"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Max Enrollments</FormLabel>
              <FormControl>
                <CustomCleaveInput
                  className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                  onChange={(rawValue: string) => {
                    const parsedInt = parseInt(rawValue.replace(/\D/g, ""), 10);
                    field.onChange(isNaN(parsedInt) ? 0 : parsedInt);
                  }}
                  options={{
                    numeral: true,
                    numeralDecimalScale: 0,
                    numeralThousandsGroupStyle: "thousand",
                    numeralPositiveOnly: true,
                    prefix: "",
                    rawValueTrimPrefix: false,
                  }}
                  placeholder="0"
                  value={String(field.value ?? "")}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="virtualLink"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Virtual Link</FormLabel>
              <FormControl>
                <Input
                  {...field}
                  placeholder="https://example.com/meeting"
                  type="url"
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="notes"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Notes</FormLabel>
              <FormControl>
                <Textarea {...field} placeholder="Additional info..." />
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
                  checked={Boolean(field.value)}
                  onCheckedChange={field.onChange}
                />
              </FormControl>
            </FormItem>
          )}
        />

        <div className="flex justify-end">
          <Button
            disabled={loading || isSubmitting || maxEnrollmentsValue < 0}
            type="submit"
          >
            {loading || isSubmitting ? "Saving..." : "Save"}
          </Button>
        </div>
      </form>
    </Form>
  );
}

export default EventSessionForm;
