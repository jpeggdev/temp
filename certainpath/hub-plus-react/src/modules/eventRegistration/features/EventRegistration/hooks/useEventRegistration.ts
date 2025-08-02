// useEventRegistration.ts

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { useNotification } from "@/context/NotificationContext";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { useNavigate, useParams } from "react-router-dom";
import {
  getEventCheckoutSessionDetailsAction,
  updateEventCheckoutSessionAction,
  resetEventCheckoutState,
} from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutSlice";

// 1) Include an optional 'id' field in each attendee
export const eventRegistrationSchema = z.object({
  contactName: z.string().min(1, "Contact name is required"),
  contactEmail: z
    .string()
    .email("Must be a valid email")
    .min(1, "Contact email is required"),
  contactPhone: z.string().optional(),
  groupNotes: z.string().optional(),
  attendees: z
    .array(
      z.object({
        // The new optional 'id' field
        id: z.number().optional(),

        firstName: z.string().min(1, "First name is required"),
        lastName: z.string().min(1, "Last name is required"),
        email: z.string().email("Invalid email").optional(),
        isSelected: z.boolean().default(true),
        specialRequests: z.string().optional(),
      }),
    )
    .default([]),
});

export type EventRegistrationFormData = z.infer<typeof eventRegistrationSchema>;

export function useEventRegistration() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { showNotification } = useNotification();
  const { eventCheckoutSessionUuid } = useParams<{
    eventCheckoutSessionUuid: string;
  }>();
  const {
    loadingGetDetails,
    getDetailsError,
    eventCheckoutSessionDetails,
    loadingUpdate,
    updateError,
  } = useAppSelector((state) => state.eventCheckout);

  const [existingCheckoutSessionUuid, setExistingCheckoutSessionUuid] =
    useState<string | null>(null);

  const form = useForm<EventRegistrationFormData>({
    resolver: zodResolver(eventRegistrationSchema),
    defaultValues: {
      contactName: "",
      contactEmail: "",
      contactPhone: "",
      groupNotes: "",
      attendees: [],
    },
    mode: "onChange",
  });

  const { handleSubmit, watch, reset, formState } = form;

  useEffect(() => {
    if (eventCheckoutSessionUuid) {
      dispatch(
        getEventCheckoutSessionDetailsAction(
          eventCheckoutSessionUuid,
          (data) => {
            if (data.uuid) {
              setExistingCheckoutSessionUuid(data.uuid);

              // 2) Include the 'id' field when resetting the form state
              reset({
                contactName: data.contactName || "",
                contactEmail: data.contactEmail || "",
                contactPhone: data.contactPhone || "",
                groupNotes: data.groupNotes || "",
                attendees:
                  data.attendees?.map((attendee) => ({
                    id: attendee.id, // preserve the ID from the server
                    firstName: attendee.firstName,
                    lastName: attendee.lastName,
                    email: attendee.email || undefined,
                    isSelected: attendee.isSelected, // or default to true, if needed
                    specialRequests: attendee.specialRequests || undefined,
                  })) || [],
              });
            }
          },
        ),
      );
    }
    return () => {
      dispatch(resetEventCheckoutState());
    };
  }, [eventCheckoutSessionUuid, dispatch, reset]);

  // Watch for form changes
  const attendees = watch("attendees");
  const selectedAttendeeCount = attendees.filter((a) => a.isSelected).length;
  const hasSelectedAttendees = selectedAttendeeCount > 0;
  const isFormReady =
    formState.isValid && hasSelectedAttendees && !loadingUpdate;

  function hasDuplicateEmails(data: EventRegistrationFormData): {
    hasDuplicates: boolean;
    message: string;
  } {
    const emailMap = new Map<string, number>();
    const duplicateEmails: string[] = [];

    data.attendees.forEach((attendee, index) => {
      if (attendee.isSelected && attendee.email) {
        const normalizedEmail = attendee.email.toLowerCase().trim();
        if (emailMap.has(normalizedEmail)) {
          duplicateEmails.push(normalizedEmail);
        } else {
          emailMap.set(normalizedEmail, index);
        }
      }
    });

    if (duplicateEmails.length > 0) {
      return {
        hasDuplicates: true,
        message: `Duplicate email${
          duplicateEmails.length > 1 ? "s" : ""
        } found: ${duplicateEmails.join(
          ", ",
        )}. Please ensure all selected attendees have unique email addresses.`,
      };
    }

    return { hasDuplicates: false, message: "" };
  }

  function onSubmit(data: EventRegistrationFormData) {
    const { hasDuplicates, message } = hasDuplicateEmails(data);
    if (hasDuplicates) {
      showNotification("Duplicate Emails Detected", message, "error");
      return;
    }
    if (existingCheckoutSessionUuid) {
      // 3) The data we dispatch now includes 'id' in each attendee,
      //    so the server can match existing records vs. new ones
      dispatch(
        updateEventCheckoutSessionAction(
          existingCheckoutSessionUuid,
          data,
          (res) => {
            navigate(
              `/event-registration/events/register/${res?.uuid}/checkout`,
            );
          },
        ),
      );
    } else {
      showNotification(
        "Session Error",
        "We didn't find a valid checkout session. Please go back and try again.",
        "error",
      );
    }
  }

  const isSubmitting = loadingUpdate;
  const getDetailsData = eventCheckoutSessionDetails;

  return {
    form,
    handleSubmit,
    onSubmit,
    getDetailsData,
    getDetailsLoading: loadingGetDetails,
    getDetailsError,
    isSubmitting,
    updateError,
    selectedAttendeeCount,
    hasSelectedAttendees,
    isFormReady,
    existingCheckoutSessionUuid,
  };
}
