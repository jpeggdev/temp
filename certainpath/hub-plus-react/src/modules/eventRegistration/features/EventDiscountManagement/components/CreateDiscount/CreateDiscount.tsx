"use client";

import React from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import "react-phone-number-input/style.css";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Textarea } from "@/components/ui/textarea";
import { DatePicker } from "@/components/DatePicker/DatePicker";
import { useCreateDiscount } from "@/modules/eventRegistration/features/EventDiscountManagement/hooks/useCreateDiscount";
import { EntityMultiSelect } from "@/components/EntityMultiSelect/EntityMultiSelect";
import { fetchEvents } from "@/modules/eventRegistration/features/EventManagement/api/fetchEvents/fetchEventsApi";
import { Switch } from "@/components/ui/switch";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import CustomCleaveInput from "@/components/CustomCleaveInput/CustomCleaveInput";

function CreateDiscount() {
  const {
    form,
    submitForm,
    isLoading,
    discountMetadata,
    handleCancelCreateDiscount,
    discountCodeCheckStatus,
    discountCodeCheckMessage,
  } = useCreateDiscount();

  const { control, watch, handleSubmit, formState } = form;
  const { isSubmitting } = formState;

  const selectedType = watch("discountType");
  const isPercentage = selectedType?.name === "percentage";

  function parseIsoString(value: string | null | undefined): Date | null {
    try {
      if (!value) return null;
      return new Date(value);
    } catch {
      return null;
    }
  }

  const handleDateChange =
    (fieldName: "startDate" | "endDate") => (selectedDate: Date | null) => {
      if (!selectedDate) {
        form.setValue(fieldName, "");
      } else {
        form.setValue(fieldName, selectedDate.toISOString());
      }
    };

  return (
    <>
      <MainPageWrapper loading={isLoading} title="">
        <div>
          <Form {...form}>
            <form
              className="space-y-8 pb-24 bg-white"
              onSubmit={handleSubmit(submitForm)}
            >
              <fieldset>
                <Card>
                  <CardHeader>
                    <CardTitle className="text-xl">
                      Create New Discount
                    </CardTitle>
                    <p className="text-sm text-gray-500">
                      Create a new discount code that can be applied during
                      event registration checkout.
                    </p>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="code"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Discount Code</FormLabel>
                            <FormControl>
                              <Input
                                {...field}
                                placeholder="Enter Discount Code..."
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      {discountCodeCheckMessage && (
                        <p
                          className={`text-xs mt-1 ${
                            discountCodeCheckStatus === "invalid"
                              ? "text-red-500"
                              : "text-green-500"
                          }`}
                        >
                          {discountCodeCheckMessage}
                        </p>
                      )}
                      <p className="text-sm text-gray-500">
                        A unique code that users will enter during checkout
                      </p>
                    </div>

                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="description"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Description</FormLabel>
                            <FormControl>
                              <Textarea
                                {...field}
                                className="w-full min-h-[75px] h-auto"
                                placeholder="Enter Discount Description..."
                                value={field.value ?? ""}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <p className="text-sm text-gray-500">
                        Additional details about this discount
                      </p>
                    </div>

                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="discountType"
                        render={({ field }) => {
                          const selected =
                            field.value ||
                            discountMetadata?.discountTypes?.find(
                              (t) => t.isDefault,
                            );

                          return (
                            <FormItem>
                              <FormLabel>Discount Type</FormLabel>
                              <FormControl>
                                <RadioGroup
                                  className="space-y-2"
                                  onValueChange={(val) => {
                                    const selectedType =
                                      discountMetadata?.discountTypes?.find(
                                        (t) => t.id.toString() === val,
                                      );
                                    field.onChange(selectedType);
                                  }}
                                  value={selected?.id.toString()}
                                >
                                  {discountMetadata?.discountTypes?.map(
                                    (type) => (
                                      <div
                                        className="flex items-center space-x-2"
                                        key={type.id}
                                      >
                                        <RadioGroupItem
                                          className="h-4 w-4"
                                          id={`discount-type-${type.id}`}
                                          value={type.id.toString()}
                                        />
                                        <Label
                                          htmlFor={`discount-type-${type.id}`}
                                        >
                                          {type.displayName}
                                        </Label>
                                      </div>
                                    ),
                                  )}
                                </RadioGroup>
                              </FormControl>
                              <FormMessage />
                            </FormItem>
                          );
                        }}
                      />
                    </div>

                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="discountValue"
                        render={({ field }) => {
                          return (
                            <FormItem>
                              <FormLabel>
                                {isPercentage
                                  ? "Discount Percentage (%)"
                                  : "Discount Amount ($)"}
                              </FormLabel>
                              <FormControl>
                                <CustomCleaveInput
                                  className="border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-600"
                                  inputMode="decimal"
                                  key={
                                    isPercentage
                                      ? "discount-v-percentage"
                                      : "discount-v-amount"
                                  }
                                  onChange={(rawValue) => {
                                    field.onChange(rawValue);
                                  }}
                                  onPaste={(e) => e.preventDefault()}
                                  options={{
                                    numeral: true,
                                    numeralDecimalScale: isPercentage ? 0 : 2,
                                    numeralThousandsGroupStyle: isPercentage
                                      ? "none"
                                      : "thousand",
                                    prefix: isPercentage ? "%" : "$",
                                    rawValueTrimPrefix: true,
                                    numeralPositiveOnly: true,
                                    tailPrefix: isPercentage,
                                  }}
                                  value={String(field.value ?? "")}
                                />
                              </FormControl>
                              <FormMessage />
                              <p className="text-sm text-gray-500">
                                {isPercentage
                                  ? "Percentage discount to apply to the total"
                                  : "Fixed amount discount to apply to the total"}
                              </p>
                            </FormItem>
                          );
                        }}
                      />
                    </div>

                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="minPurchaseAmount"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Minimum Purchase Amount</FormLabel>
                            <FormControl>
                              <CustomCleaveInput
                                className="border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-600"
                                inputMode="decimal"
                                onChange={(rawValue) => {
                                  field.onChange(rawValue);
                                }}
                                onPaste={(e) => e.preventDefault()}
                                options={{
                                  numeral: true,
                                  numeralDecimalScale: 2,
                                  numeralThousandsGroupStyle: "thousand",
                                  prefix: "$",
                                  rawValueTrimPrefix: true,
                                  numeralPositiveOnly: true,
                                }}
                                value={String(field.value ?? "")}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <p className="text-sm text-gray-500">
                        Optional minimum purchase amount required to use this
                        discount. Leave blank for no minimum
                      </p>
                    </div>

                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="maxUses"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Maximum Uses</FormLabel>
                            <FormControl>
                              <CustomCleaveInput
                                className="border border-gray-300 rounded py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-600"
                                inputMode="decimal"
                                onChange={(rawValue) => {
                                  field.onChange(rawValue);
                                }}
                                onPaste={(e) => e.preventDefault()}
                                options={{
                                  numeral: true,
                                  numeralDecimalScale: 0,
                                  numeralThousandsGroupStyle: "none",
                                  prefix: "",
                                  rawValueTrimPrefix: true,
                                  numeralPositiveOnly: true,
                                }}
                                value={String(field.value ?? "")}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <p className="text-sm text-gray-500">
                        Optional maximum number of times this discount code can
                        be used. Leave blank for unlimited
                      </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
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
                                    showTimeSelect
                                    value={dateValue}
                                  />
                                </FormControl>
                                <FormMessage />
                              </FormItem>
                            );
                          }}
                        />
                        <p className="text-sm text-gray-500">
                          Leave blank for no start date
                        </p>
                      </div>

                      <div className="space-y-2">
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
                                    showTimeSelect
                                    value={dateValue}
                                  />
                                </FormControl>
                                <FormMessage />
                              </FormItem>
                            );
                          }}
                        />
                        <p className="text-sm text-gray-500">
                          Leave blank for no expiration date
                        </p>
                      </div>
                    </div>

                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="events"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Apply to Events</FormLabel>
                            <FormControl>
                              <EntityMultiSelect
                                entityNamePlural="Events"
                                entityNameSingular="Event"
                                fetchEntities={async ({
                                  searchTerm,
                                  page,
                                  pageSize,
                                }) => {
                                  const response = await fetchEvents({
                                    searchTerm,
                                    page,
                                    pageSize,
                                    sortBy: "eventName",
                                    sortOrder: "asc",
                                  });
                                  const events = response.data;
                                  const totalCount =
                                    response.meta?.totalCount ?? 0;
                                  return {
                                    data: events.map((e) => ({
                                      id: e.id,
                                      name: e.eventName,
                                    })),
                                    totalCount,
                                  };
                                }}
                                isFullWidth={true}
                                onChange={field.onChange}
                                value={field.value || []}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <p className="text-sm text-gray-500">
                        Leave blank to apply to all events
                      </p>
                    </div>

                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="isActive"
                        render={({ field }) => (
                          <FormItem className="flex items-center justify-between rounded-lg border p-4">
                            <div className="space-y-0.5">
                              <FormLabel className="text-base">
                                Active
                              </FormLabel>
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
                    </div>

                    <div className="sticky bottom-0 flex justify-end gap-4 px-4 py-4 border-t bg-white mt-8">
                      <Button
                        onClick={handleCancelCreateDiscount}
                        type="button"
                        variant="outline"
                      >
                        Cancel
                      </Button>
                      <Button type="submit">
                        {isSubmitting || isLoading
                          ? "Creating..."
                          : "Create Discount"}
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </fieldset>
            </form>
          </Form>
        </div>
      </MainPageWrapper>
    </>
  );
}

export default CreateDiscount;
