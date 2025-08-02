import { useRef, useState, useCallback, useEffect } from "react";
import { UseFormReturn } from "react-hook-form";
import { useDispatch, useSelector } from "react-redux";
import { AppDispatch } from "@/app/store";
import { fetchEmailTemplateVariablesAction } from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateVariableListSlice";
import { RootState } from "@/app/rootReducer";
import { EmailTemplateFormData } from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/emailTemplateFormSchema";

export function useEmailTemplateEditor(
  form: UseFormReturn<EmailTemplateFormData>,
) {
  const iframeRef = useRef<HTMLIFrameElement>(null);
  const [activeTab, setActiveTab] = useState<"edit" | "preview">("edit");
  const { control, getValues, setValue } = form;
  const { emailTemplateVariables } = useSelector(
    (state: RootState) => state.emailTemplateVariableList,
  );
  const dispatch = useDispatch<AppDispatch>();

  useEffect(() => {
    dispatch(fetchEmailTemplateVariablesAction());
  }, [dispatch]);

  const adjustIframeHeight = useCallback(() => {
    if (iframeRef.current) {
      const doc = iframeRef.current.contentWindow?.document;
      if (doc) iframeRef.current.style.height = `${doc.body.scrollHeight}px`;
    }
  }, []);

  const handleInsertVariable = (variableName: string) => {
    const textarea = document.querySelector(
      "textarea[name='emailContent']",
    ) as HTMLTextAreaElement;
    if (!textarea) return;

    const currentContent = getValues("emailContent");
    const { selectionStart, selectionEnd } = textarea;
    const newContent =
      currentContent.slice(0, selectionStart) +
      variableName +
      currentContent.slice(selectionEnd);

    setValue("emailContent", newContent);

    requestAnimationFrame(() => {
      textarea.selectionStart = textarea.selectionEnd =
        selectionStart + variableName.length;
      textarea.focus();
    });
  };

  return {
    emailTemplateVariables,
    control,
    getValues,
    iframeRef,
    activeTab,
    setActiveTab,
    adjustIframeHeight,
    handleInsertVariable,
  };
}
