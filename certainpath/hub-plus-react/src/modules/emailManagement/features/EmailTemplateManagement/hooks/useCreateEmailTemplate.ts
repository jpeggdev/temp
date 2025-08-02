import React, { useCallback, useState } from "react";
import { useDispatch } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useToast } from "@/components/ui/use-toast";
import { createEmailTemplateAction } from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateSlice";
import {
  EmailTemplateFormData,
  EmailTemplateFormSchema,
} from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/emailTemplateFormSchema";

export function useCreateEmailTemplate() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { toast } = useToast();
  const { showNotification } = useNotification();
  const [isLoading, setIsLoading] = useState(false);

  const defaultValues: EmailTemplateFormData = {
    templateName: "",
    emailSubject: "",
    emailContent: "",
    categories: [],
  };

  const form = useForm<EmailTemplateFormData>({
    resolver: zodResolver(EmailTemplateFormSchema),
    defaultValues,
    mode: "onChange",
  });

  const submitForm = useCallback(
    async (values: EmailTemplateFormData) => {
      try {
        setIsLoading(true);

        const categoryIds = (values.categories || []).map((c) => c.id);

        const requestData = {
          templateName: values.templateName,
          emailSubject: values.emailSubject,
          emailContent: values.emailContent,
          categoryIds: categoryIds,
        };

        dispatch(
          createEmailTemplateAction(requestData, () => {
            showNotification(
              "Success!",
              "Email template has been successfully created!",
              "success",
            );
            navigate(`/email-management/email-templates`);
          }),
        );
      } catch (error) {
        console.error("Error saving email template:", error);
        if (error instanceof Error) {
          toast({
            title: "Error",
            description: error.message || "Failed to save",
            variant: "destructive",
          });
        } else {
          toast({
            title: "Error",
            description: "An unknown error occurred while saving.",
            variant: "destructive",
          });
        }
      } finally {
        setIsLoading(false);
      }
    },
    [dispatch, toast, navigate, showNotification],
  );

  const handleCancelCreateEmailTemplate = (e: React.FormEvent) => {
    e.preventDefault();

    navigate("/email-management/email-templates");
  };

  return {
    form,
    submitForm,
    handleCancelCreateEmailTemplate,
    isLoading,
  };
}
