import React, { useState, useEffect } from "react";
import { RichTextEditorLinkDialogProps } from "@/modules/hub/features/ResourceManagement/components/RichTextEditorLinkDialog/types";
import { Button } from "@/components/ui/button";

export function RichTextEditorLinkDialog({
  isOpen,
  onClose,
  onSubmit,
  initialUrl = "https://",
  initialText = "",
  showTextInput = false,
}: RichTextEditorLinkDialogProps) {
  const [url, setUrl] = useState(initialUrl);
  const [text, setText] = useState(initialText);

  useEffect(() => {
    if (isOpen) {
      setUrl(initialUrl);
      setText(initialText);
    }
  }, [isOpen, initialUrl, initialText]);

  const handleSave = () => {
    onSubmit(url, showTextInput ? text : undefined);
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div
      style={{
        position: "absolute",
        backgroundColor: "#fff",
        border: "1px solid #ccc",
        padding: "1rem",
        zIndex: 9999,
      }}
    >
      <h2 style={{ fontWeight: "bold" }}>Insert Link</h2>
      <p>Enter the URL for your link. Make sure to include https://</p>

      <div style={{ display: "grid", gap: "0.5rem" }}>
        {showTextInput && (
          <div>
            <label>Link Text</label>
            <input
              onChange={(e) => setText(e.target.value)}
              style={{
                width: "100%",
                padding: "6px",
                border: "1px solid #ccc",
                borderRadius: 4,
              }}
              type="text"
              value={text}
            />
          </div>
        )}

        <div>
          <label>URL</label>
          <input
            autoFocus
            onChange={(e) => setUrl(e.target.value)}
            style={{
              width: "100%",
              padding: "6px",
              border: "1px solid #ccc",
              borderRadius: 4,
            }}
            type="url"
            value={url}
          />
        </div>

        <div style={{ display: "flex", justifyContent: "flex-end", gap: 8 }}>
          <Button onClick={onClose} type="button" variant="outline">
            Cancel
          </Button>
          <Button onClick={handleSave} type="button" variant="default">
            Save
          </Button>
        </div>
      </div>
    </div>
  );
}
