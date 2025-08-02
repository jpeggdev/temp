import React, { useState, useCallback } from "react";
import {
  useRemirrorContext,
  useCommands,
  useActive,
  useKeymap,
} from "@remirror/react";
import {
  BoldIcon,
  ItalicIcon,
  UnderlineIcon,
  Heading2,
  ListIcon,
  ListOrderedIcon,
  LinkIcon,
  Trash2Icon,
} from "lucide-react";
import { RichTextEditorMenuButton } from "@/modules/hub/features/ResourceManagement/components/RichTextEditorMenuButton/RichTextEditorMenuButton";
import { RichTextEditorLinkDialog } from "@/modules/hub/features/ResourceManagement/components/RichTextEditorLinkDialog/RichTextEditorLinkDialog";
import { EditorState } from "prosemirror-state";

interface LinkMarkAttributes {
  href?: string;
  [key: string]: unknown;
}

export function RichTextEditorToolbar() {
  const { manager } = useRemirrorContext({ autoUpdate: true });
  const commands = useCommands();
  const active = useActive();

  const [linkDialogOpen, setLinkDialogOpen] = useState(false);
  const [currentLinkUrl, setCurrentLinkUrl] = useState("https://");
  const [currentLinkText, setCurrentLinkText] = useState("");
  const [showTextInput, setShowTextInput] = useState(false);

  useKeymap("Mod-k", () => {
    addLink();
    return true;
  });

  const findLinkAttributesInSelection = useCallback(
    (state: EditorState): LinkMarkAttributes | null => {
      const { from, to } = state.selection;
      if (from === to) return null;
      const linkMarkType = state.schema.marks.link;
      let foundAttrs: LinkMarkAttributes | null = null;

      state.doc.nodesBetween(from, to, (node) => {
        if (!node.isText) return;
        const linkMark = node.marks.find((mark) => mark.type === linkMarkType);
        if (linkMark) {
          foundAttrs = linkMark.attrs as LinkMarkAttributes;
          return false;
        }
      });
      return foundAttrs;
    },
    [],
  );

  const addLink = useCallback(() => {
    const view = manager.view;
    if (!view) return;
    const { state } = view;
    const { from, to, empty } = state.selection;

    if (active.link()) {
      const existingAttrs = findLinkAttributesInSelection(state);
      const existingHref = existingAttrs?.href || "https://";
      setCurrentLinkUrl(existingHref);
      setShowTextInput(false);
      setLinkDialogOpen(true);
      return;
    }

    if (empty) {
      const linkText = "Link text";
      commands.insertText(linkText);
      const updatedState = manager.view.state;
      commands.selectText({
        from: updatedState.selection.from - linkText.length,
        to: updatedState.selection.from,
      });
      setCurrentLinkUrl("https://");
      setCurrentLinkText(linkText);
      setShowTextInput(true);
      setLinkDialogOpen(true);
      return;
    }

    const selectedText = state.doc.textBetween(from, to);
    setCurrentLinkText(selectedText);
    setCurrentLinkUrl("https://");
    setShowTextInput(false);
    setLinkDialogOpen(true);
  }, [manager, commands, active, findLinkAttributesInSelection]);

  const handleLinkSubmit = useCallback(
    (url: string, text?: string) => {
      if (!url) return;
      const view = manager.view;
      if (!view) return;
      const { state } = view;
      const { from, to } = state.selection;
      if (text) {
        commands.replaceText({
          range: { from, to },
          content: text,
        });
        commands.selectText({ from, to: from + text.length });
      }
      commands.updateLink({ href: url, auto: false });
    },
    [manager, commands],
  );

  const removeLink = useCallback(() => {
    if (!active.link()) {
      commands.selectLink();
      if (!active.link()) return;
    }
    commands.removeLink();
  }, [commands, active]);

  const buttons = [
    {
      label: "Bold",
      action: () => commands.toggleBold(),
      isActive: active.bold(),
      icon: <BoldIcon size={16} />,
    },
    {
      label: "Italic",
      action: () => commands.toggleItalic(),
      isActive: active.italic(),
      icon: <ItalicIcon size={16} />,
    },
    {
      label: "Underline",
      action: () => commands.toggleUnderline(),
      isActive: active.underline(),
      icon: <UnderlineIcon size={16} />,
    },
    {
      label: "Heading 2",
      action: () => commands.toggleHeading({ level: 2 }),
      isActive: active.heading({ level: 2 }),
      icon: <Heading2 size={16} />,
    },
    {
      label: "Bullet List",
      action: () => commands.toggleBulletList(),
      isActive: active.bulletList(),
      icon: <ListIcon size={16} />,
    },
    {
      label: "Ordered List",
      action: () => commands.toggleOrderedList(),
      isActive: active.orderedList(),
      icon: <ListOrderedIcon size={16} />,
    },
    {
      label: "Link",
      action: addLink,
      isActive: active.link(),
      icon: <LinkIcon size={16} />,
    },
  ];

  return (
    <>
      <div className="flex items-center p-2 border-b" role="toolbar">
        {buttons.map((btn) => (
          <RichTextEditorMenuButton
            isActive={btn.isActive}
            key={btn.label}
            onClick={btn.action}
          >
            {btn.icon}
          </RichTextEditorMenuButton>
        ))}
        {active.link() && (
          <RichTextEditorMenuButton onClick={removeLink}>
            <Trash2Icon size={16} />
          </RichTextEditorMenuButton>
        )}
      </div>
      <RichTextEditorLinkDialog
        initialText={currentLinkText}
        initialUrl={currentLinkUrl}
        isOpen={linkDialogOpen}
        onClose={() => setLinkDialogOpen(false)}
        onSubmit={handleLinkSubmit}
        showTextInput={showTextInput}
      />
    </>
  );
}
