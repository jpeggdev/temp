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
import { useCreateEmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/useCreateEmailCampaign";
import { Check } from "lucide-react";
import { fetchEmailTemplates } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplates/fetchEmailTemplatesApi";
import { EntitySingleSelect } from "@/components/EntitySingleSelect/EntitySingleSelect";
import { formatDate } from "@/utils/dateUtils";
import { EnvelopeIcon } from "@heroicons/react/24/outline";
import SendTestEmailModal from "@/modules/emailManagement/features/EmailCampaignManagement/components/SendTestEmailModal/SendTestEmailModal";
import { fetchEmailCampaignSendOptions } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaignSendOptions/fetchEmailCampaignSendOptionsApi";
import RecipientCountCard from "@/modules/emailManagement/features/EmailCampaignManagement/components/RecipientCountCard/RecipientCountCard";
import { FetchEventsLookupResponse } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEventsLookup/types";
import { fetchEventsLookup } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEventsLookup/fetchEventsLookupApi";
import { FetchEventSessionsLookupResponse } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEventSessionsLookup/types";
import { fetchEventSessionsLookup } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEventSessionsLookup/fetchEventSessionsLookupApi";

function CreateEmailCampaign() {
  const {
    form,
    submitForm,
    isLoading,
    recipientCount,
    emailTemplateName,
    isSendTestEmailModalOpen,
    handleCancelCreateEmailTemplate,
    handleSendTestEmailModalVisibility,
  } = useCreateEmailCampaign();

  const { control, handleSubmit, formState } = form;
  const { isSubmitting, isValid: isFormValid } = formState;

  return (
    <MainPageWrapper loading={isLoading} title="">
      <div className="">
        <Form {...form}>
          <form
            className="space-y-8 pb-24 bg-white"
            onSubmit={handleSubmit(submitForm)}
          >
            <fieldset>
              <Card>
                <CardHeader>
                  <CardTitle className="text-xl">
                    Create Email Campaign
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                  <h3 className="text-lg font-semibold mt-4">
                    Campaign Details
                  </h3>

                  {/* EMAIL CAMPAIGN NAME SELECTION */}
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="name"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Campaign Name</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter Campaign Name"
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      A descriptive name for your campaign
                    </p>
                  </div>

                  {/* EMAIL CAMPAIGN DESCRIPTION SELECTION */}
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
                              placeholder="Enter Campaign Description"
                              value={field.value ?? ""}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      Additional details about this campaign
                    </p>
                  </div>

                  <h3 className="text-lg font-semibold">Content Selection</h3>

                  {/* EMAIL TEMPLATE SELECTION */}
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="emailTemplate"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Email Template</FormLabel>
                          <FormControl>
                            <EntitySingleSelect
                              entityNamePlural="Email Templates"
                              entityNameSingular="Email Template"
                              fetchEntities={async ({
                                searchTerm,
                                page,
                                pageSize,
                              }) => {
                                const response = await fetchEmailTemplates({
                                  searchTerm,
                                  page,
                                  perPage: pageSize,
                                  sortBy: "name",
                                  sortOrder: "ASC",
                                });
                                const { data } = response;
                                const totalCount =
                                  response.meta?.totalCount ?? data.length;
                                return {
                                  data: data.map((c) => ({
                                    id: c.id,
                                    name: c.templateName,
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
                              value={field.value || null}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      The email template to use for this campaign
                    </p>
                  </div>

                  {/* EMAIL SUBJECT SELECTION */}
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="emailSubject"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Custom Subject Line (Optional)</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter Custom Subject Line..."
                              value={field.value ?? ""}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      {`Overrides the template's default subject line`}
                    </p>
                  </div>

                  <h3 className="text-lg font-semibold">Audience Targeting</h3>

                  {/* EVENT SELECTION */}
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="event"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Event</FormLabel>
                          <FormControl>
                            <EntitySingleSelect
                              entityNamePlural="Events"
                              entityNameSingular="Event"
                              fetchEntities={async ({
                                searchTerm,
                                page,
                                pageSize,
                              }) => {
                                const response: FetchEventsLookupResponse =
                                  await fetchEventsLookup({
                                    searchTerm,
                                    page,
                                    pageSize,
                                    sortBy: "eventName",
                                    sortOrder: "asc",
                                  });

                                const totalCount =
                                  response.meta?.totalCount ??
                                  response.data.length;

                                return {
                                  data: response.data.map((ev) => ({
                                    id: ev.id,
                                    name: ev.name,
                                  })),
                                  totalCount,
                                };
                              }}
                              onChange={field.onChange}
                              renderEntityRow={(ent, isSelected, toggle) => (
                                <div
                                  className={`cursor-pointer flex items-center justify-between py-4 px-2 border-b last:border-0 ${
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
                              value={field.value || null}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      Select an event to filter sessions
                    </p>
                  </div>

                  {/* SESSION SELECTION */}
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="session"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Session</FormLabel>
                          <FormControl>
                            <EntitySingleSelect
                              disabled={!form.watch("event")}
                              disabledMessage="Please Select Event First..."
                              entityNamePlural="Sessions"
                              entityNameSingular="Session"
                              fetchEntities={async ({
                                searchTerm,
                                page,
                                pageSize,
                              }) => {
                                // NEW: Use fetchEventSessionsLookup
                                const eventId = form.watch("event")?.id;
                                if (!eventId) {
                                  return { data: [], totalCount: 0 };
                                }

                                const response: FetchEventSessionsLookupResponse =
                                  await fetchEventSessionsLookup({
                                    eventId,
                                    page,
                                    pageSize,
                                    sortBy: "startDate",
                                    sortOrder: "asc",
                                    searchTerm,
                                  });

                                const totalCount =
                                  response.meta?.totalCount ??
                                  response.data.length;

                                return {
                                  data: response.data.map((es) => ({
                                    id: es.id,
                                    name: formatDate(es.startDate),
                                  })),
                                  totalCount,
                                };
                              }}
                              onChange={field.onChange}
                              renderEntityRow={(ent, isSelected, toggle) => (
                                <div
                                  className={`cursor-pointer flex items-center justify-between py-4 px-2 border-b last:border-0 ${
                                    isSelected ? "bg-blue-50" : ""
                                  }`}
                                  key={ent.id}
                                  onClick={() => toggle(ent)}
                                >
                                  <span className="flex items-center gap-4">
                                    <b>Start Date:</b> {ent.name}
                                  </span>
                                  {isSelected && (
                                    <Check className="h-5 w-5 text-primary flex-shrink-0" />
                                  )}
                                </div>
                              )}
                              value={field.value || null}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      The session associated with this event
                    </p>
                  </div>

                  {/* RECIPIENTS SELECTION */}
                  <div className="space-y-2">
                    {recipientCount &&
                    typeof recipientCount.count === "number" ? (
                      <RecipientCountCard count={recipientCount.count} />
                    ) : (
                      <RecipientCountCard message="Select an Event and Session to See Recipient Count" />
                    )}
                    <p className="text-sm text-gray-500">
                      The total number of email recipients
                    </p>
                  </div>

                  <h3 className="text-lg font-semibold">Scheduling</h3>

                  {/* SEND OPTION SELECTION */}
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="sendOption"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Send Option</FormLabel>
                          <FormControl>
                            <EntitySingleSelect
                              entityNamePlural="Send Options"
                              entityNameSingular="Send Option"
                              fetchEntities={async () => {
                                const response =
                                  await fetchEmailCampaignSendOptions();
                                const { data: sendOptions } = response;
                                const totalCount =
                                  response.meta?.totalCount ??
                                  sendOptions.length;
                                return {
                                  data: sendOptions.map((e) => ({
                                    id: e.id,
                                    name: e.label,
                                  })),
                                  totalCount,
                                };
                              }}
                              onChange={field.onChange}
                              renderEntityRow={(ent, isSelected, toggle) => (
                                <div
                                  className={`cursor-pointer flex items-center gap-4 py-4 px-2 border-b last:border-0 ${
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
                              value={field.value || null}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      Choose when to send this campaign
                    </p>
                  </div>

                  <div className="sticky bottom-0 w-full border-t bg-white px-4 py-4 mt-8 z-10">
                    <div className="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
                      <div className="w-full sm:w-auto">
                        <Button
                          className="w-full sm:w-auto"
                          disabled={!isFormValid}
                          onClick={handleSendTestEmailModalVisibility}
                          type="button"
                          variant="outline"
                        >
                          <EnvelopeIcon className="w-4 h-4 mr-2" />
                          Send Test Email
                        </Button>
                      </div>

                      <SendTestEmailModal
                        emailTemplateName={emailTemplateName}
                        form={form}
                        handleCloseModal={handleSendTestEmailModalVisibility}
                        isOpen={isSendTestEmailModalOpen}
                      />

                      <div className="flex gap-4 w-full sm:w-auto">
                        <Button
                          className="w-full sm:w-auto"
                          onClick={handleCancelCreateEmailTemplate}
                          type="button"
                          variant="outline"
                        >
                          Cancel
                        </Button>
                        <Button
                          className="w-full sm:w-auto"
                          disabled={!isFormValid}
                          type="submit"
                        >
                          {isSubmitting || isLoading
                            ? "Creating..."
                            : "Create Campaign"}
                        </Button>
                      </div>
                    </div>
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

export default CreateEmailCampaign;
