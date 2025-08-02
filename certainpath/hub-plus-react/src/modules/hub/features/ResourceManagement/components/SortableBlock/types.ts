export const BLOCK_TYPES = {
  text: "text",
  image: "image",
  file: "file",
  vimeo: "vimeo",
  youtube: "youtube",
} as const;

export type BlockType = keyof typeof BLOCK_TYPES;

export interface ContentBlockBase {
  id?: string;
  type: BlockType;
  content: string;
  fileId?: number | null;
  fileUuid?: string | null;
  order_number?: number;
  title?: string;
  shortDescription?: string;
}

export interface SortableBlockProps extends ContentBlockBase {
  onRemove: () => void;
  onChange: (updates: Partial<ContentBlockBase>) => void;
}
