import React from "react";
import { Textarea } from "@/components/ui/textarea";
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { UseFormReturn } from "react-hook-form";
import ToggleButtonGroup from "@/modules/emailManagement/features/EmailTemplateManagement/components/ToggleButtonGroup/ToggleButtonGroup";
import { useEmailTemplateEditor } from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/useEmailTemplateEditor";
import EmailTemplateVariableSelector from "@/modules/emailManagement/features/EmailTemplateManagement/components/EmailTemplateVariableSelector/EmailTemplateVariableSelector";
import { EmailTemplateFormData } from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/emailTemplateFormSchema";

interface EmailEditorProps {
  form: UseFormReturn<EmailTemplateFormData>;
}

const EmailTemplateContentEditor: React.FC<EmailEditorProps> = ({ form }) => {
  const {
    control,
    getValues,
    activeTab,
    iframeRef,
    adjustIframeHeight,
    setActiveTab,
    handleInsertVariable,
  } = useEmailTemplateEditor(form);
  const emailContentValue = getValues("emailContent").trim();

  const renderEmailContentSection = () => {
    if (activeTab === "edit") {
      return (
        <FormField
          control={control}
          name="emailContent"
          render={({ field }) => (
            <FormItem className="mt-4">
              <FormControl>
                <Textarea
                  {...field}
                  className="w-full min-h-[300px] h-auto"
                  placeholder="<h1>Welcome!</h1><p>Enter your HTML content here</p>"
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
      );
    }

    if (emailContentValue) {
      return (
        <iframe
          className="w-full border rounded-md mt-4"
          onLoad={adjustIframeHeight}
          ref={iframeRef}
          sandbox="allow-same-origin"
          srcDoc={`<style>body, html { margin: 0; padding: 0; overflow: hidden; }</style>${emailContentValue}`}
          style={{ minHeight: "300px", width: "100%", border: "none" }}
        />
      );
    }

    return (
      <p className="text-gray-500 min-h-[300px] flex items-center justify-center mt-4">
        No content to preview
      </p>
    );
  };

  return (
    <>
      <FormLabel>Email Content</FormLabel>
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-2">
        <ToggleButtonGroup activeTab={activeTab} setActiveTab={setActiveTab} />
        {activeTab === "edit" && (
          <EmailTemplateVariableSelector
            onVariableSelect={handleInsertVariable}
          />
        )}
      </div>
      {renderEmailContentSection()}
      {activeTab === "edit" && (
        <p className="text-sm text-gray-500 mt-2">
          {`Enter your email template HTML. Use the "Insert Variable" button to add dynamic content that will be replaced when emails are sent.`}
        </p>
      )}
    </>
  );
};

export default EmailTemplateContentEditor;
