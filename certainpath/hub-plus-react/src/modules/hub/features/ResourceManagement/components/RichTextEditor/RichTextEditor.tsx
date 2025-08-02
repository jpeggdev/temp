import React, { useRef, useState, useCallback, useEffect } from "react";
import {
  Remirror,
  ThemeProvider,
  EditorComponent,
  useRemirror,
} from "@remirror/react";
import {
  BoldExtension,
  ItalicExtension,
  UnderlineExtension,
  MarkdownExtension,
  HeadingExtension,
  BulletListExtension,
  OrderedListExtension,
  LinkExtension,
} from "remirror/extensions";
import { RichTextEditorToolbar } from "@/modules/hub/features/ResourceManagement/components/RichTextEditorToolbar/RichTextEditorToolbar";
import { RichTextEditorProps } from "@/modules/hub/features/ResourceManagement/components/RichTextEditor/types";
import "./RichTextEditor.css";
import { RemirrorEventListenerProps } from "@remirror/core";
import { Extension } from "@remirror/core/dist-types";

export function RichTextEditor({
  onChange,
  initialContent = "",
}: RichTextEditorProps) {
  const [content, setContent] = useState(initialContent);
  const isInitialRender = useRef(true);

  useEffect(() => {
    if (initialContent) {
      setContent(initialContent);
    }
  }, [initialContent]);

  const extensions = useCallback(() => {
    return [
      new BoldExtension({}),
      new ItalicExtension({}),
      new UnderlineExtension({}),
      new MarkdownExtension({}),
      new HeadingExtension({}),
      new BulletListExtension({}),
      new OrderedListExtension({}),
      new LinkExtension({
        autoLink: true,
        openLinkOnClick: true,
        defaultTarget: "_blank",
      }),
    ];
  }, []);

  const { manager, state } = useRemirror({
    extensions,
    content,
    stringHandler: "markdown",
  });

  const handleEditorChange = useCallback(
    (parameter: RemirrorEventListenerProps<Extension>) => {
      const markdown = parameter.helpers.getMarkdown();
      if (isInitialRender.current) {
        isInitialRender.current = false;
        return;
      }
      if (markdown !== content) {
        setContent(markdown);
        onChange(markdown);
      }
    },
    [content, onChange],
  );

  const editorRef = useRef<HTMLDivElement>(null);
  const handleClick = useCallback(() => {
    if (editorRef.current) {
      const editor = editorRef.current.querySelector(
        "[role='textbox']",
      ) as HTMLElement;
      if (editor) {
        editor.focus();
      }
    }
  }, []);

  return (
    <div
      className="border rounded-lg"
      onClick={handleClick}
      ref={editorRef}
      style={{ marginTop: 8 }}
    >
      <ThemeProvider>
        <Remirror
          autoFocus={false}
          classNames={["min-h-[150px] p-4 my-rich-text-editor"]}
          initialContent={state}
          manager={manager}
          onChange={handleEditorChange}
        >
          <EditorComponent />
          <RichTextEditorToolbar />
        </Remirror>
      </ThemeProvider>
    </div>
  );
}
