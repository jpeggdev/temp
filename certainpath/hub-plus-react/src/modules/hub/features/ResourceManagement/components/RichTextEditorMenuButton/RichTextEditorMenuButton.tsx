import React from "react";
import { RichTextEditorMenuButtonProps } from "@/modules/hub/features/ResourceManagement/components/RichTextEditorMenuButton/types";

export function RichTextEditorMenuButton({
  onClick,
  isActive = false,
  children,
}: RichTextEditorMenuButtonProps) {
  return (
    <button
      aria-pressed={isActive}
      className={`p-2 text-sm font-medium rounded hover:bg-blue-100 ${
        isActive ? "bg-blue-100 text-blue-700" : "text-gray-900"
      }`}
      onClick={onClick}
      type="button"
    >
      {children}
    </button>
  );
}
