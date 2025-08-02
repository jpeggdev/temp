import { ContentBlockBase } from "@/modules/hub/features/ResourceManagement/components/SortableBlock/types";

export interface ContentBlockEditorProps {
  blocks: ContentBlockBase[];
  onChange: (blocks: ContentBlockBase[]) => void;
}
