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
import { Check } from "lucide-react";
import { useCreateVoucher } from "@/modules/eventRegistration/features/EventVoucherManagement/hooks/useCreateVoucher";
import { Textarea } from "@/components/ui/textarea";
import { DatePicker } from "@/components/DatePicker/DatePicker";
import { fetchCompanies } from "@/api/fetchCompanies/fetchCompaniesApi";
import { EntitySingleSelect } from "@/components/EntitySingleSelect/EntitySingleSelect";
import { Switch } from "@/components/ui/switch";
import { ApiCompany } from "@/api/fetchCompanies/types";

function CreateVoucher() {
  const {
    form,
    submitForm,
    isLoading,
    voucherNameCheckStatus,
    voucherNameCheckMessage,
    parseIsoString,
    handleDateChange,
    handleCancelCreateVoucher,
  } = useCreateVoucher();

  const { control, handleSubmit, formState } = form;
  const { isSubmitting } = formState;

  return (
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
                  <CardTitle className="text-xl">Create New Voucher</CardTitle>
                  <p className="text-sm text-gray-500">
                    Create a new voucher that provides registration seats for a
                    company.
                  </p>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="name"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Voucher Name</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter Voucher Name..."
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    {voucherNameCheckMessage && (
                      <p
                        className={`text-xs mt-1 ${
                          voucherNameCheckStatus === "invalid"
                            ? "text-red-500"
                            : "text-green-500"
                        }`}
                      >
                        {voucherNameCheckMessage}
                      </p>
                    )}
                    <p className="text-sm text-gray-500">
                      A unique name that identifies this voucher
                    </p>
                  </div>

                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="company"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Company</FormLabel>
                          <FormControl>
                            <EntitySingleSelect
                              entityNamePlural="Companies"
                              entityNameSingular="Company"
                              fetchEntities={async ({
                                searchTerm,
                                page,
                                pageSize,
                              }) => {
                                const response = await fetchCompanies({
                                  searchTerm,
                                  page,
                                  pageSize,
                                  sortBy: "companyName",
                                  sortOrder: "ASC",
                                });

                                const { data } = response;
                                const totalCount: number =
                                  response.meta?.totalCount ??
                                  data.companies.length;

                                return {
                                  data: data.companies.map((c: ApiCompany) => ({
                                    id: c.id,
                                    name: c.companyName,
                                    companyIdentifier: c.intacctId ?? "",
                                  })),
                                  totalCount,
                                };
                              }}
                              onChange={field.onChange}
                              renderEntityRow={(ent, isSelected, toggle) => (
                                <div
                                  className={`cursor-pointer flex justify-between items-center gap-4 py-4 px-2 border-b last:border-0 ${
                                    isSelected ? "bg-blue-50" : ""
                                  }`}
                                  key={ent.id}
                                  onClick={() => toggle(ent)}
                                >
                                  <span>{ent.name}</span>
                                  {isSelected && (
                                    <Check className="h-5 w-5 text-primary flex-shrink-0" />
                                  )}
                                </div>
                              )}
                              value={
                                field.value
                                  ? {
                                      id: field.value.id,
                                      name: field.value.name,
                                      companyIdentifier:
                                        field.value.companyIdentifier,
                                    }
                                  : null
                              }
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      The company whose members can use this voucher
                    </p>
                  </div>

                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="description"
                      render={({ field }) => (
                        <FormItem className="mt-4">
                          <FormLabel>Description (Optional)</FormLabel>
                          <FormControl>
                            <Textarea
                              {...field}
                              className="w-full min-h-[75px] h-auto"
                              placeholder="Enter Voucher Description..."
                              value={field.value ?? ""}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      Additional details about this voucher
                    </p>
                  </div>

                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="totalSeats"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Number of Seats</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              onChange={(e) =>
                                field.onChange(e.target.valueAsNumber)
                              }
                              placeholder="Enter Number of Seats..."
                              type="number"
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      How many seats (registrations) this voucher provides
                    </p>
                  </div>

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
                                placeholder="Pick a start date/time"
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
                      When the voucher becomes valid (leave blank for immediate
                      validity)
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
                                placeholder="Pick an end date/time"
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
                      When the voucher expires (Leave blank for no expiration
                      date)
                    </p>
                  </div>

                  <FormField
                    control={control}
                    name="isActive"
                    render={({ field }) => (
                      <FormItem className="flex items-center justify-between rounded-lg border p-4">
                        <div className="space-y-0.5">
                          <FormLabel className="text-base">Active</FormLabel>
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

                  <div className="sticky bottom-0 flex justify-end gap-4 px-4 py-4 border-t bg-white mt-8">
                    <Button
                      onClick={handleCancelCreateVoucher}
                      type="button"
                      variant="outline"
                    >
                      Cancel
                    </Button>
                    <Button type="submit">
                      {isSubmitting || isLoading
                        ? "Creating..."
                        : "Create Voucher"}
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </fieldset>
          </form>
        </Form>
      </div>
    </MainPageWrapper>
  );
}

export default CreateVoucher;
