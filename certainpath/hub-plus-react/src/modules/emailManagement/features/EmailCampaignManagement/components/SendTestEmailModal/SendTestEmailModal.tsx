import React from "react";
import Modal from "react-modal";
import { Button } from "@/components/Button/Button";
import { Input } from "@/components/ui/input";
import { FormLabel } from "@/components/ui/form";
import { X } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/utils/utils";
import { UseFormReturn } from "react-hook-form";
import { EmailCampaignFormData } from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/emailCampaignFormSchema";
import { useSendTestEmail } from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/useSendTestEmail";

export interface SendTestEmailModalProps {
  isOpen: boolean;
  handleCloseModal: () => void;
  emailTemplateName: string;
  form: UseFormReturn<EmailCampaignFormData>;
}

export default function SendTestEmailModal({
  isOpen,
  handleCloseModal,
  emailTemplateName,
  form,
}: SendTestEmailModalProps) {
  const {
    email,
    emailList,
    emailError,
    canSendPreviewEmail,
    setEmail,
    handleAddEmail,
    handleRemoveEmail,
    handleSendTestEmail,
    resetEmailList,
  } = useSendTestEmail({ form, handleCloseModal });

  const closeModalAndClearEmailList = () => {
    handleCloseModal();
    resetEmailList();
  };

  return (
    <Modal
      className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[70vw] sm:w-[50vw] md:w-[40vw] h-[60vh] sm:h-[50vh] flex flex-col rounded-lg p-0 bg-white shadow-xl"
      isOpen={isOpen}
      onRequestClose={closeModalAndClearEmailList}
      overlayClassName="fixed inset-0 bg-black bg-opacity-30 z-[9999]"
    >
      <div className="flex-shrink-0 p-4 border-b flex flex-col">
        <h2 className="text-lg font-semibold text-secondary">
          Send Test Email
        </h2>
        <p className="text-sm text-gray-500 mt-1">
          Send a test email using the <b>{emailTemplateName}</b> template to
          verify how it will appear to recipients
        </p>
      </div>

      <div className="flex-1 overflow-auto p-4" id="add-recipient">
        <FormLabel
          className={cn("block mb-2", emailError && "text-destructive")}
        >
          Recipient Email Addresses
        </FormLabel>
        <div className="flex gap-2 mb-2">
          <Input
            className="flex-1"
            onChange={(e) => setEmail(e.target.value)}
            placeholder="Enter recipient email"
            value={email}
          />
          <Button className="h-9" onClick={handleAddEmail}>
            Add
          </Button>
        </div>

        {emailError && (
          <p className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-destructive">
            {emailError}
          </p>
        )}

        <div className="flex flex-wrap gap-2 mt-4">
          {emailList.map((emailItem, index) => (
            <Badge className="truncate flex items-center space-x-1" key={index}>
              <span className="text-white">{emailItem}</span>
              <X
                className="h-3 w-3 cursor-pointer text-white"
                onClick={() => handleRemoveEmail(emailItem)}
              />
            </Badge>
          ))}
        </div>
      </div>

      <div className="flex-shrink-0 p-4 border-t flex justify-end space-x-3">
        <Button
          onClick={closeModalAndClearEmailList}
          type="button"
          variant="outline"
        >
          Cancel
        </Button>
        <Button
          className="bg-primary text-white hover:bg-primary-dark"
          disabled={canSendPreviewEmail}
          onClick={handleSendTestEmail}
          type="button"
        >
          Send Preview
        </Button>
      </div>
    </Modal>
  );
}
