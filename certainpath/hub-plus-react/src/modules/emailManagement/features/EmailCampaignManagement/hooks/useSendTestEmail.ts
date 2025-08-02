import { useState } from "react";
import { sendTestEmail } from "@/modules/emailManagement/features/EmailCampaignManagement/api/sendTestEmail/sendTestEmailApi";
import { SendTestEmailRequest } from "@/modules/emailManagement/features/EmailCampaignManagement/api/sendTestEmail/types";
import { useNotification } from "@/context/NotificationContext";
import { UseFormReturn } from "react-hook-form";
import { EmailCampaignFormData } from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/emailCampaignFormSchema";

interface UseSendTestEmailProps {
  form: UseFormReturn<EmailCampaignFormData>;
  handleCloseModal: () => void;
}

export function useSendTestEmail({
  form,
  handleCloseModal,
}: UseSendTestEmailProps) {
  const [email, setEmail] = useState("");
  const [emailList, setEmailList] = useState<string[]>([]);
  const [emailError, setEmailError] = useState<string | null>(null);
  const { showNotification } = useNotification();

  const validateEmail = (email: string): boolean => {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
  };

  const handleAddEmail = () => {
    if (email) {
      if (validateEmail(email)) {
        setEmailList((prevList) => [...prevList, email]);
        setEmail("");
        setEmailError(null);
      } else {
        setEmailError("Please enter a valid email address.");
      }
    }
  };

  const handleRemoveEmail = (emailToRemove: string) => {
    setEmailList((prevList) =>
      prevList.filter((email) => email !== emailToRemove),
    );
  };

  const handleSendTestEmail = async () => {
    const { emailSubject, emailTemplate, event, session } = form.getValues();

    const requestData: SendTestEmailRequest = {
      emailRecipients: emailList,
      emailSubject,
      emailTemplateId: emailTemplate!.id,
      eventId: event!.id,
      sessionId: session!.id,
    };

    try {
      const response = await sendTestEmail(requestData);

      showNotification("Success", `Test email successfully sent!`, "success");
      console.log("Test email sent successfully", response);
      handleCloseModal();
      resetEmailList();
    } catch (err) {
      showNotification("Error", `Failed to send test email!`, "error");
      console.error("Error sending test email:", err);
      handleCloseModal();
      resetEmailList();
    }
  };

  const resetEmailList = () => {
    setEmailList([]);
  };

  return {
    email,
    emailList,
    emailError,
    canSendPreviewEmail: emailList.length === 0,
    setEmail,
    handleAddEmail,
    handleRemoveEmail,
    handleSendTestEmail,
    resetEmailList,
  };
}
