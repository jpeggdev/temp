import React from "react";
import { useNavigate } from "react-router-dom";
import { ArrowLeft, Loader2, ArrowRight } from "lucide-react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/ui/button";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import EventContactPersonForm from "@/modules/eventRegistration/features/EventRegistration/components/EventContactPersonForm/EventContactPersonForm";
import AttendeeList from "@/modules/eventRegistration/features/EventRegistration/components/AttendeeList/AttendeeList";
import {
  useEventRegistration,
  EventRegistrationFormData,
} from "@/modules/eventRegistration/features/EventRegistration/hooks/useEventRegistration";
import { PriceSummary } from "@/modules/eventRegistration/features/EventRegistration/components/PriceSummary/PriceSummary";
import EventDetails from "@/modules/eventRegistration/features/EventRegistration/components/EventDetails/EventDetails";
import SelectAttendeesLoadingSkeleton from "@/modules/eventRegistration/features/EventRegistration/components/SelectAttendeesLoadingSkeleton/SelectAttendeesLoadingSkeleton";
import { ContinueSessionModal } from "@/modules/eventRegistration/features/EventRegistration/components/ContinueSessionModal /ContinueSessionModal";
import { useEventCheckoutReservation } from "@/modules/eventRegistration/features/EventRegistration/hooks/useEventCheckoutReservation";

function SelectAttendees() {
  const navigate = useNavigate();
  const {
    form,
    onSubmit,
    getDetailsLoading,
    getDetailsError,
    getDetailsData,
    isSubmitting,
    selectedAttendeeCount,
    hasSelectedAttendees,
    isFormReady,
    existingCheckoutSessionUuid,
  } = useEventRegistration();

  const { handleSubmit, control } = form;
  const eventPrice = getDetailsData?.eventPrice ?? 0;

  // If the API returns "occupiedAttendeeSeatsByCurrentUser", we can choose to factor that
  // in our displayed "availableSeats," but typically we show "availableSeats" from the API.
  // E.g., if user is already holding seats, the "availableSeats" might be reduced accordingly.
  const availableSeats =
    typeof getDetailsData?.availableSeats === "number"
      ? getDetailsData.availableSeats
      : 0;

  const {
    timeLeft,
    showSessionModal,
    isContinuing,
    handleContinueSession,
    handleStartNewSession,
    formatTimeLeft,
  } = useEventCheckoutReservation({
    existingCheckoutSessionUuid,
    reservationExpiresAt: getDetailsData?.reservationExpiresAt ?? null,
    eventSessionUuid: getDetailsData?.eventSessionUuid ?? null,
    getDetailsData,
  });

  function handleFinalSubmit(data: EventRegistrationFormData) {
    onSubmit(data);
  }

  if (getDetailsLoading) {
    return <SelectAttendeesLoadingSkeleton />;
  }

  return (
    <>
      <MainPageWrapper hideHeader title="Register Attendees">
        <div className="mb-6">
          <Button
            className="px-0 flex items-center gap-2 text-muted-foreground hover:text-primary"
            onClick={() =>
              navigate(
                `/event-registration/events/${getDetailsData?.eventUuid}`,
              )
            }
            variant="link"
          >
            <ArrowLeft className="mr-1 h-4 w-4" />
            Back to Event
          </Button>
        </div>

        {getDetailsError && (
          <Alert className="mb-4" variant="destructive">
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{getDetailsError}</AlertDescription>
          </Alert>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-1">
            <EventDetails
              availableSeats={availableSeats}
              eventPrice={eventPrice}
              existingCheckoutSessionUuid={existingCheckoutSessionUuid}
              formatTimeLeft={formatTimeLeft}
              getDetailsData={getDetailsData}
              timeLeft={timeLeft}
            />
          </div>

          <div className="lg:col-span-2">
            <Form {...form}>
              <form onSubmit={handleSubmit(handleFinalSubmit)}>
                <Card>
                  <CardHeader>
                    <CardTitle>Register for Event</CardTitle>
                    <CardDescription>
                      Please provide contact information and attendee details
                    </CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-6">
                    <EventContactPersonForm />
                    <AttendeeList />
                    <FormField
                      control={control}
                      name="groupNotes"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Group Notes (Optional)</FormLabel>
                          <FormControl>
                            <Textarea
                              placeholder="Any additional information for the entire group"
                              {...field}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </CardContent>
                  <div className="px-6 pb-4">
                    <PriceSummary
                      attendeeCount={selectedAttendeeCount}
                      price={eventPrice}
                    />
                  </div>
                  <CardFooter className="flex flex-col gap-2">
                    {!hasSelectedAttendees && (
                      <div className="text-sm text-destructive self-end mb-1">
                        Please select at least one attendee
                      </div>
                    )}
                    <Button
                      className="self-end"
                      disabled={!isFormReady}
                      type="submit"
                      variant="default"
                    >
                      {isSubmitting ? (
                        <>
                          <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                          Processing...
                        </>
                      ) : (
                        <>
                          Continue to Checkout
                          <ArrowRight className="ml-2 h-4 w-4" />
                        </>
                      )}
                    </Button>
                  </CardFooter>
                </Card>
              </form>
            </Form>
          </div>
        </div>
      </MainPageWrapper>

      <ContinueSessionModal
        createdAt={getDetailsData?.reservationExpiresAt ?? null}
        eventName={getDetailsData?.eventName ?? "Event"}
        isContinuing={isContinuing}
        isOpen={showSessionModal}
        onContinue={handleContinueSession}
        onStartNew={handleStartNewSession}
      />
    </>
  );
}

export default SelectAttendees;
