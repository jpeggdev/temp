import React, { useEffect, useState, useCallback } from "react";
import { useDispatch } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useToast } from "@/components/ui/use-toast";
import {
  fetchEmailCampaignAction,
  fetchEmailCampaignRecipientCountAction,
  resetRecipientCount,
  updateEmailCampaignAction,
} from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignSlice";
import {
  EmailCampaignFormData,
  EmailCampaignFormSchema,
} from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/emailCampaignFormSchema";
import { useAppSelector } from "@/app/hooks";
import { formatDate } from "@/utils/dateUtils";

export function useUpdateEmailCampaign() {
  const dispatch = useDispatch<AppDispatch>();
  const navigate = useNavigate();
  const { toast } = useToast();
  const { showNotification } = useNotification();

  const [isSendTestEmailModalOpen, setIsSendTestEmailModalOpen] =
    useState(false);

  const { recipientCount, fetchedEmailCampaign, loadingFetch } = useAppSelector(
    (state) => state.emailCampaign,
  );

  const form = useForm<EmailCampaignFormData>({
    resolver: zodResolver(EmailCampaignFormSchema),
    defaultValues: {
      name: "",
      description: "",
      emailTemplate: null,
      emailSubject: null,
      event: null,
      session: null,
      sendOption: null,
    },
    mode: "onChange",
  });

  useEffect(() => {
    if (!fetchedEmailCampaign) return;

    form.reset({
      name: fetchedEmailCampaign.campaignName || "",
      description: fetchedEmailCampaign.description || null,
      emailSubject: fetchedEmailCampaign.emailSubject,
      emailTemplate: fetchedEmailCampaign.emailTemplate
        ? {
            id: fetchedEmailCampaign.emailTemplate.id,
            name: fetchedEmailCampaign.emailTemplate.templateName,
          }
        : null,
      event: fetchedEmailCampaign.event
        ? {
            id: fetchedEmailCampaign.event.id,
            name: fetchedEmailCampaign.event.eventName,
          }
        : null,
      session: fetchedEmailCampaign.eventSession
        ? {
            id: fetchedEmailCampaign.eventSession.id,
            name: formatDate(fetchedEmailCampaign.eventSession.startDate),
          }
        : null,
      sendOption: fetchedEmailCampaign.sendOption
        ? {
            id: fetchedEmailCampaign.sendOption.id,
            name: fetchedEmailCampaign.sendOption.label,
          }
        : null,
    });
  }, [fetchedEmailCampaign, form]);

  const selectedEvent = form.watch("event");
  const selectedSession = form.watch("session");
  const emailTemplateName = form.watch("emailTemplate")?.name ?? "";

  useEffect(() => {
    if (selectedEvent?.id) {
      form.setValue("session", null);
      dispatch(resetRecipientCount());
    }
  }, [selectedEvent?.id, dispatch, form]);

  useEffect(() => {
    if (selectedSession?.id) {
      dispatch(fetchEmailCampaignRecipientCountAction(selectedSession.id));
    }
  }, [selectedSession?.id, dispatch]);

  const submitForm = useCallback(
    async (values: EmailCampaignFormData) => {
      const requestData = {
        campaignName: values.name,
        description: values.description,
        emailSubject: values.emailSubject,
        emailTemplateId: values.emailTemplate!.id,
        eventId: values.event!.id,
        sessionId: values.session!.id,
        sendOption: values.sendOption!.name,
      };

      try {
        if (!fetchedEmailCampaign) return;
        console.log("Failed to fetch Email Campaign.");

        dispatch(
          updateEmailCampaignAction(
            fetchedEmailCampaign.id,
            requestData,
            () => {
              showNotification(
                "Success!",
                "Email campaign has been successfully updated!",
                "success",
              );
              navigate("/email-management/email-campaigns");
            },
          ),
        );
      } catch (error) {
        const errorMessage =
          error instanceof Error ? error.message : "An unknown error occurred.";
        toast({
          title: "Error",
          description: errorMessage,
          variant: "destructive",
        });
      }
    },
    [dispatch, toast, navigate, showNotification],
  );

  const handleCancelUpdateEmailCampaign = (e: React.FormEvent) => {
    e.preventDefault();
    navigate("/email-management/email-campaigns");
  };

  const handleSendTestEmailModalVisibility = () => {
    setIsSendTestEmailModalOpen((prev) => !prev);
  };

  const fetchEmailCampaign = useCallback(
    (idParam: number) => {
      dispatch(fetchEmailCampaignAction(idParam));
    },
    [dispatch],
  );

  return {
    form,
    recipientCount,
    loadingFetch,
    fetchEmailCampaign,
    emailTemplateName,
    submitForm,
    isSendTestEmailModalOpen,
    handleCancelUpdateEmailCampaign,
    handleSendTestEmailModalVisibility,
  };
}
