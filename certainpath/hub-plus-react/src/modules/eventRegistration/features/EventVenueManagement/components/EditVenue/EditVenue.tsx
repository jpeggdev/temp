"use client";

import React, { useEffect, useMemo } from "react";
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
import { useParams } from "react-router-dom";
import { Textarea } from "@/components/ui/textarea";
import { useEditVenue } from "@/modules/eventRegistration/features/EventVenueManagement/hooks/useEditVenue";

function EditVenue() {
  const {
    form,
    submitForm,
    isLoading,
    venueName,
    fetchVenue,
    handleCancelEditVenue,
  } = useEditVenue();

  const { control, handleSubmit, formState } = form;
  const { isSubmitting } = formState;
  const { venueId } = useParams<{ venueId: string }>();

  useEffect(() => {
    if (venueId) {
      fetchVenue(Number(venueId));
    }
  }, [venueId, fetchVenue]);

  const manualBreadcrumbs = useMemo(() => {
    if (!venueName) return undefined;
    const editVenueName = venueName || `Venue ${venueId}`;
    return [
      { path: "/event-registration/admin/venues", label: "Venues" },
      {
        path: `/event-registration/admin/venue/${venueId}/edit`,
        label: `Editing ${editVenueName}`,
        clickable: false,
      },
    ];
  }, [venueId, venueName]);

  return (
    <MainPageWrapper
      loading={isLoading}
      manualBreadcrumbs={manualBreadcrumbs}
      title=""
    >
      <div>
        <Form {...form}>
          <form
            className="space-y-8 pb-24 bg-white"
            onSubmit={handleSubmit(submitForm)}
          >
            <fieldset>
              <Card>
                <CardHeader>
                  <CardTitle className="text-xl">Edit Venue</CardTitle>
                  <p className="text-sm text-gray-500">
                    Edit the exising venue
                  </p>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="name"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Venue Name</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter Venue Name..."
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
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
                              placeholder="Enter Venue Description..."
                              value={field.value ?? ""}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="address"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Address Line 1</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter Address Line 1..."
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                  <div className="space-y-2">
                    <FormField
                      control={control}
                      name="address2"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Address Line 2</FormLabel>
                          <FormControl>
                            <Input
                              {...field}
                              placeholder="Enter Address Line 2..."
                              value={field.value ?? ""}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="city"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>City</FormLabel>
                            <FormControl>
                              <Input {...field} placeholder="Enter City..." />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>
                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="state"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>State</FormLabel>
                            <FormControl>
                              <Input {...field} placeholder="Enter State..." />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>
                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="postalCode"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Postal Code</FormLabel>
                            <FormControl>
                              <Input
                                {...field}
                                placeholder="Enter Postal Code..."
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>
                    <div className="space-y-2">
                      <FormField
                        control={control}
                        name="country"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Country</FormLabel>
                            <FormControl>
                              <Input
                                {...field}
                                placeholder="Enter Country..."
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>
                  </div>

                  <div className="sticky bottom-0 flex justify-end gap-4 px-4 py-4 border-t bg-white mt-8">
                    <Button
                      onClick={handleCancelEditVenue}
                      type="button"
                      variant="outline"
                    >
                      Cancel
                    </Button>
                    <Button disabled={isLoading} type="submit">
                      {isSubmitting || isLoading ? "Saving..." : "Save Venue"}
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

export default EditVenue;
