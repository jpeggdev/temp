export interface RichTextEditorProps {
  onChange: (content: string) => void;
  initialContent?: string;
  id: string;
  onRemove: () => void;
}
