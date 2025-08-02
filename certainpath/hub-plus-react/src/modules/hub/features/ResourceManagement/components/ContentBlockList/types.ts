import { ContentBlockBase } from "@/modules/hub/features/ResourceManagement/components/SortableBlock/types";

export interface ContentBlockListProps {
  blocks: ContentBlockBase[];
  onChange: (blocks: ContentBlockBase[]) => void;
}
