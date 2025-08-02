import React, { useEffect, useState, useCallback } from "react";
import { useDispatch } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useToast } from "@/components/ui/use-toast";
import {
  createEmailCampaignAction,
  fetchEmailCampaignRecipientCountAction,
  resetRecipientCount,
} from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignSlice";
import {
  EmailCampaignFormData,
  EmailCampaignFormSchema,
} from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/emailCampaignFormSchema";
import { useAppSelector } from "@/app/hooks";

export function useCreateEmailCampaign() {
  const dispatch = useDispatch<AppDispatch>();
  const navigate = useNavigate();
  const { toast } = useToast();
  const { showNotification } = useNotification();

  const [isLoading, setIsLoading] = useState(false);
  const [isSendTestEmailModalOpen, setIsSendTestEmailModalOpen] =
    useState(false);

  const { recipientCount } = useAppSelector((state) => state.emailCampaign);

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
      setIsLoading(true);

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
        dispatch(
          createEmailCampaignAction(requestData, () => {
            showNotification(
              "Success!",
              "Email campaign has been successfully created!",
              "success",
            );
            navigate("/email-management/email-campaigns");
          }),
        );
      } catch (error) {
        const errorMessage =
          error instanceof Error ? error.message : "An unknown error occurred.";
        toast({
          title: "Error",
          description: errorMessage,
          variant: "destructive",
        });
      } finally {
        setIsLoading(false);
      }
    },
    [dispatch, toast, navigate, showNotification],
  );

  const handleCancelCreateEmailTemplate = (e: React.FormEvent) => {
    e.preventDefault();
    navigate("/email-management/email-campaigns");
  };

  const handleSendTestEmailModalVisibility = () => {
    setIsSendTestEmailModalOpen((prev) => !prev);
  };

  return {
    form,
    recipientCount,
    emailTemplateName,
    submitForm,
    isLoading,
    isSendTestEmailModalOpen,
    handleCancelCreateEmailTemplate,
    handleSendTestEmailModalVisibility,
  };
}
