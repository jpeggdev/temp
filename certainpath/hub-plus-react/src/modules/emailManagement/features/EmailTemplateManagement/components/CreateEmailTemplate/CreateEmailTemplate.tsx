"use client";

import React from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import "react-phone-number-input/style.css";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { EntityMultiSelect } from "@/components/EntityMultiSelect/EntityMultiSelect";
import { fetchEmailTemplateCategories } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateCategories/fetchEmailTemplateCategoriesApi";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import EmailTemplateContentEditor from "@/modules/emailManagement/features/EmailTemplateManagement/components/EmailTemplateContentEditor/EmailTemplateContentEditor";
import { useCreateEmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/useCreateEmailTemplate";
import { Check, CircleIcon } from "lucide-react";

function CreateEmailTemplate() {
  const { form, submitForm, isLoading, handleCancelCreateEmailTemplate } =
    useCreateEmailTemplate();

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
                  <CardTitle className="text-xl">
                    Create New Email Template
                  </CardTitle>
                  <p className="text-sm text-gray-500">
                    Create a new email template for your campaign
                  </p>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="templateName"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Template Name</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter template name..."
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      A unique name to identify this template
                    </p>
                  </div>

                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="emailSubject"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Email Subject</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter email subject..."
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <p className="text-sm text-gray-500">
                      The subject line of the email
                    </p>
                  </div>

                  <div className="space-y-2">
                    <EmailTemplateContentEditor form={form} />
                  </div>

                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="categories"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Categories</FormLabel>
                          <FormControl>
                            <EntityMultiSelect
                              entityNamePlural="Categories"
                              entityNameSingular="Category"
                              fetchEntities={async ({
                                searchTerm,
                                page,
                                pageSize,
                              }) => {
                                const response =
                                  await fetchEmailTemplateCategories({
                                    name: searchTerm,
                                    page,
                                    pageSize,
                                    sortBy: "name",
                                    sortOrder: "ASC",
                                  });
                                const { data: catData } = response;
                                const totalCount =
                                  response.meta?.totalCount ?? catData.length;
                                return {
                                  data: catData.map((c) => ({
                                    id: c.id,
                                    name: c.displayedName,
                                    color: c.color,
                                  })),
                                  totalCount,
                                };
                              }}
                              isFullWidth={true}
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
                                    <div className="flex items-center gap-2">
                                      {ent.color?.value && (
                                        <CircleIcon
                                          className="h-4 w-4 shrink-0"
                                          fill={ent.color.value}
                                          stroke="none"
                                        />
                                      )}
                                      <span>{ent.name}</span>
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
                    <p className="text-sm text-gray-500">
                      Select multiple categories to organize your templates. You
                      can add or remove categories by clicking on them.
                    </p>
                  </div>

                  <div className="sticky bottom-0 flex justify-end gap-4 px-4 py-4 border-t bg-white mt-8">
                    <Button
                      onClick={handleCancelCreateEmailTemplate}
                      type="button"
                      variant="outline"
                    >
                      Cancel
                    </Button>
                    <Button type="submit">
                      {isSubmitting || isLoading
                        ? "Creating..."
                        : "Create Template"}
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

export default CreateEmailTemplate;
