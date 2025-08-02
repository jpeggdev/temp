import React from "react";

export interface RichTextEditorMenuButtonProps {
  onClick: () => void;
  isActive?: boolean;
  children: React.ReactNode;
}
