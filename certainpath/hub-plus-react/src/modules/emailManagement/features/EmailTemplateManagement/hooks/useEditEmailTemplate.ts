import { useCallback, useEffect, useState } from "react";
import { useDispatch } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useToast } from "@/components/ui/use-toast";
import { useAppSelector } from "@/app/hooks";
import {
  fetchEmailTemplateAction,
  updateEmailTemplateAction,
} from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateSlice";
import {
  EmailTemplateFormData,
  EmailTemplateFormSchema,
} from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/emailTemplateFormSchema";

export function useEditEmailTemplate() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { toast } = useToast();
  const { showNotification } = useNotification();
  const [isLoading, setIsLoading] = useState(false);

  const { fetchedEmailTemplate, updateLoading, fetchLoading } = useAppSelector(
    (state) => state.emailTemplate,
  );

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

  useEffect(() => {
    if (fetchedEmailTemplate) {
      form.reset({
        templateName: fetchedEmailTemplate.templateName || "",
        emailSubject: fetchedEmailTemplate.emailSubject || "",
        emailContent: fetchedEmailTemplate.emailContent || "",
        categories:
          fetchedEmailTemplate.emailTemplateCategories?.map((c) => ({
            id: c.id,
            name: c.name,
            displayName: c.displayedName,
            color: c.color,
          })) || [],
      });
    }
  }, [fetchedEmailTemplate, form]);

  const submitForm = useCallback(
    async (values: EmailTemplateFormData) => {
      try {
        setIsLoading(true);

        if (!fetchedEmailTemplate) {
          throw new Error("No email template loaded to update");
        }
        const emailTemplateId = fetchedEmailTemplate.id;
        if (!emailTemplateId) {
          throw new Error("No email template ID found to update");
        }

        const categoryIds = values.categories?.map((c) => c.id) || [];

        const requestData = {
          templateName: values.templateName,
          emailSubject: values.emailSubject,
          emailContent: values.emailContent,
          categoryIds: categoryIds,
        };

        dispatch(
          updateEmailTemplateAction(emailTemplateId, requestData, () => {
            showNotification(
              "Success!",
              "Email template has been successfully updated!",
              "success",
            );
            navigate(`/email-management/email-templates`);
          }),
        );
      } catch (error) {
        console.error("Error updating email template:", error);
        if (error instanceof Error) {
          toast({
            title: "Error",
            description: error.message || "Failed to update email template",
            variant: "destructive",
          });
        } else {
          toast({
            title: "Error",
            description:
              "An unknown error occurred while updating email template.",
            variant: "destructive",
          });
        }
      } finally {
        setIsLoading(false);
      }
    },
    [dispatch, fetchedEmailTemplate, toast],
  );

  const fetchEmailTemplate = useCallback(
    (idParam: number) => {
      dispatch(fetchEmailTemplateAction(idParam));
    },
    [dispatch],
  );

  const handleCancelEditEmailTemplate = useCallback(() => {
    navigate("/email-management/email-templates");
  }, []);

  return {
    form,
    submitForm,
    fetchEmailTemplate,
    handleCancelEditEmailTemplate,
    isLoading: isLoading || updateLoading || fetchLoading,
    templateName: fetchedEmailTemplate?.templateName,
  };
}
