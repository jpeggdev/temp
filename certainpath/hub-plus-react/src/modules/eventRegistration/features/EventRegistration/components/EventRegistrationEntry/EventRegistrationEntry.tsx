import React, { useEffect, useRef, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import { RootState } from "@/app/rootReducer";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";

import {
  getInProgressEventCheckoutSessionAction,
  resetEventCheckoutSessionReservationExpirationAction,
  resetEventCheckoutEntryState,
} from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutEntrySlice";

import {
  createEventCheckoutSessionAction,
  resetEventCheckoutState,
} from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutSlice";
import EventRegistrationEntryLoadingSkeleton from "@/modules/eventRegistration/features/EventRegistration/components/EventRegistrationEntryLoadingSkeleton/EventRegistrationEntryLoadingSkeleton";
import { ContinueSessionModal } from "@/modules/eventRegistration/features/EventRegistration/components/ContinueSessionModal /ContinueSessionModal";

function EventRegistrationEntry() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { eventSessionUuid } = useParams();

  const { inProgressError, inProgressData, resetReservationError } =
    useSelector((state: RootState) => state.eventCheckoutEntry);

  const { createError } = useSelector(
    (state: RootState) => state.eventCheckout,
  );

  const [isContinuing, setIsContinuing] = useState(false);
  const hasInitializedRef = useRef(false);

  useEffect(() => {
    if (!eventSessionUuid) return;
    if (hasInitializedRef.current) return;
    hasInitializedRef.current = true;

    dispatch(
      getInProgressEventCheckoutSessionAction(eventSessionUuid, (data) => {
        if (!data?.uuid) {
          createNewSession();
        }
      }),
    );

    return () => {
      dispatch(resetEventCheckoutEntryState());
      dispatch(resetEventCheckoutState());
    };
  }, [eventSessionUuid, dispatch]);

  function createNewSession() {
    if (!eventSessionUuid) return;
    dispatch(
      createEventCheckoutSessionAction({ eventSessionUuid }, (created) => {
        if (created?.uuid) {
          navigate(
            `/event-registration/events/register/${created.uuid}/attendees`,
          );
        }
      }),
    );
  }

  function handleContinue() {
    if (!inProgressData?.uuid) return;
    setIsContinuing(true);

    dispatch(
      resetEventCheckoutSessionReservationExpirationAction(
        { eventCheckoutSessionUuid: inProgressData.uuid },
        () => {
          setIsContinuing(false);
          navigate(
            `/event-registration/events/register/${inProgressData.uuid}/attendees`,
          );
        },
      ),
    );
  }

  function handleStartNew() {
    createNewSession();
  }

  return (
    <MainPageWrapper title="Event Registration">
      <EventRegistrationEntryLoadingSkeleton />

      {inProgressError && (
        <Alert className="my-4" variant="destructive">
          <AlertTitle>Error</AlertTitle>
          <AlertDescription>{inProgressError}</AlertDescription>
        </Alert>
      )}

      {inProgressData?.uuid && (
        <ContinueSessionModal
          createdAt={inProgressData.createdAt}
          eventName={inProgressData.eventName || ""}
          isContinuing={isContinuing}
          isOpen
          onContinue={handleContinue}
          onStartNew={handleStartNew}
        />
      )}

      {createError && (
        <Alert className="my-4" variant="destructive">
          <AlertTitle>Error</AlertTitle>
          <AlertDescription>{createError}</AlertDescription>
        </Alert>
      )}

      {resetReservationError && (
        <Alert className="my-4" variant="destructive">
          <AlertTitle>Error</AlertTitle>
          <AlertDescription>{resetReservationError}</AlertDescription>
        </Alert>
      )}
    </MainPageWrapper>
  );
}

export default EventRegistrationEntry;
