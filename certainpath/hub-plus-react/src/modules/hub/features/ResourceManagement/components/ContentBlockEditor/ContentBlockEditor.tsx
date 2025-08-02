import React, { useCallback, useRef } from "react";
import { Button } from "@/components/ui/button";
import { ContentBlockList } from "@/modules/hub/features/ResourceManagement/components/ContentBlockList/ContentBlockList";
import { ContentBlockEditorProps } from "@/modules/hub/features/ResourceManagement/components/ContentBlockEditor/types";
import {
  BlockType,
  ContentBlockBase,
} from "@/modules/hub/features/ResourceManagement/components/SortableBlock/types";

export function ContentBlockEditor({
  blocks,
  onChange,
}: ContentBlockEditorProps) {
  const listContainerRef = useRef<HTMLDivElement>(null);

  const handleAddBlock = useCallback(
    (blockType: BlockType) => {
      const newBlock: ContentBlockBase = {
        id: crypto.randomUUID(),
        type: blockType,
        content: "",
        order_number: blocks.length,
      };

      const updated = [...blocks, newBlock];
      onChange(updated);

      setTimeout(() => {
        if (listContainerRef.current) {
          listContainerRef.current.scrollTo({
            top: listContainerRef.current.scrollHeight,
            behavior: "smooth",
          });
        }
        window.scrollTo({
          top: document.body.scrollHeight,
          behavior: "smooth",
        });
      }, 0);
    },
    [blocks, onChange],
  );

  const handleBlocksChange = useCallback(
    (updatedBlocks: ContentBlockBase[]) => {
      onChange(updatedBlocks);
    },
    [onChange],
  );

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap gap-2">
        <Button
          onClick={() => handleAddBlock("text")}
          size="sm"
          type="button"
          variant="outline"
        >
          Add Text
        </Button>
        <Button
          onClick={() => handleAddBlock("image")}
          size="sm"
          type="button"
          variant="outline"
        >
          Add Image
        </Button>
        <Button
          onClick={() => handleAddBlock("file")}
          size="sm"
          type="button"
          variant="outline"
        >
          Add File
        </Button>
        <Button
          onClick={() => handleAddBlock("vimeo")}
          size="sm"
          type="button"
          variant="outline"
        >
          Add Vimeo
        </Button>
        <Button
          onClick={() => handleAddBlock("youtube")}
          size="sm"
          type="button"
          variant="outline"
        >
          Add YouTube
        </Button>
      </div>

      <ContentBlockList
        blocks={blocks}
        onChange={handleBlocksChange}
        ref={listContainerRef}
      />
    </div>
  );
}
