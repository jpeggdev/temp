import React, { useCallback, useMemo, forwardRef } from "react";
import {
  DndContext,
  DragEndEvent,
  closestCenter,
  PointerSensor,
  KeyboardSensor,
  useSensor,
  useSensors,
} from "@dnd-kit/core";
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import { UniqueIdentifier } from "@dnd-kit/core";
import { SortableBlock } from "@/modules/hub/features/ResourceManagement/components/SortableBlock/SortableBlock";
import { ContentBlockListProps } from "@/modules/hub/features/ResourceManagement/components/ContentBlockList/types";
import { ContentBlockBase } from "@/modules/hub/features/ResourceManagement/components/SortableBlock/types";

export const ContentBlockList = forwardRef<
  HTMLDivElement,
  ContentBlockListProps
>(function ContentBlockList({ blocks, onChange }, ref) {
  const filteredBlocks = useMemo(() => blocks, [blocks]);

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  const handleDragEnd = useCallback(
    (event: DragEndEvent) => {
      const { active, over } = event;
      if (!over || active.id === over.id) return;

      const oldIndex = filteredBlocks.findIndex((b) => b.id === active.id);
      const newIndex = filteredBlocks.findIndex((b) => b.id === over.id);
      if (oldIndex < 0 || newIndex < 0) return;

      const newBlocks = arrayMove(filteredBlocks, oldIndex, newIndex).map(
        (block, i) => ({ ...block, order_number: i }),
      );
      onChange(newBlocks);
    },
    [filteredBlocks, onChange],
  );

  const handleRemoveBlock = useCallback(
    (blockId: string) => {
      onChange(filteredBlocks.filter((b) => b.id !== blockId));
    },
    [filteredBlocks, onChange],
  );

  const handleUpdateBlock = useCallback(
    (blockId: string, updates: Partial<ContentBlockBase>) => {
      const newBlocks = filteredBlocks.map((b) =>
        b.id === blockId ? { ...b, ...updates } : b,
      );
      onChange(newBlocks);
    },
    [filteredBlocks, onChange],
  );

  return (
    <DndContext
      collisionDetection={closestCenter}
      onDragEnd={handleDragEnd}
      sensors={sensors}
    >
      <SortableContext
        items={filteredBlocks.map((b) => b.id as UniqueIdentifier)}
        strategy={verticalListSortingStrategy}
      >
        <div className="space-y-4" ref={ref}>
          {filteredBlocks.map((block, i) => (
            <SortableBlock
              key={block.id || `temp-${i}`}
              {...block}
              onChange={(updates) => handleUpdateBlock(block.id || "", updates)}
              onRemove={() => handleRemoveBlock(block.id || "")}
            />
          ))}

          {filteredBlocks.length === 0 && (
            <div className="text-center py-8 text-gray-500 border border-dashed rounded-lg">
              No content blocks yet. Add blocks above.
            </div>
          )}
        </div>
      </SortableContext>
    </DndContext>
  );
});
