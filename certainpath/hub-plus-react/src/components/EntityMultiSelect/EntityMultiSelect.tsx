"use client";

import React, { useState, useMemo, useEffect } from "react";
import { X } from "lucide-react";
import { PlusIcon } from "@heroicons/react/24/outline";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/components/ui/lib/utils";
import EntityPickerModal, {
  BaseEntity,
  FetchEntitiesFn,
  CreateEntityFn,
} from "@/components/EntityPickerModal/EntityPickerModal";

interface EntityMultiSelectProps<T extends BaseEntity> {
  value: T[];
  onChange: (newValue: T[]) => void;
  fetchEntities: FetchEntitiesFn<T>;
  createEntity?: CreateEntityFn<T>;
  entityNameSingular?: string;
  entityNamePlural?: string;
  renderEntityRow?: (
    entity: T,
    isSelected: boolean,
    toggle: (ent: T) => void,
  ) => React.ReactNode;
  isFullWidth?: boolean;
}

export function EntityMultiSelect<T extends BaseEntity>({
  value,
  onChange,
  fetchEntities,
  createEntity,
  entityNameSingular,
  entityNamePlural,
  renderEntityRow,
  isFullWidth = false,
}: EntityMultiSelectProps<T>) {
  const [selectedEntities, setSelectedEntities] = useState<T[]>([]);
  const [modalOpen, setModalOpen] = useState(false);

  useEffect(() => {
    setSelectedEntities(value);
  }, [value]);

  const handleRemoveItem = (id: string | number) => {
    const newEntities = selectedEntities.filter((x) => x.id !== id);
    setSelectedEntities(newEntities);
    onChange(newEntities);
  };

  const handleConfirm = (newSelected: T[]) => {
    setSelectedEntities(newSelected);
    onChange(newSelected);
    setModalOpen(false);
  };

  const chips = useMemo(
    () =>
      selectedEntities.map((ent) => ({
        id: ent.id,
        displayName: ent.name,
        color: ent.color,
      })),
    [selectedEntities],
  );

  return (
    <div className="space-y-2">
      <div className="flex flex-wrap gap-2">
        {chips.map((chip) => (
          <Badge
            className="truncate flex items-center space-x-1"
            key={chip.id}
            style={{
              backgroundColor: chip.color?.value || "",
            }}
          >
            <span className="text-white">{chip.displayName}</span>
            <X
              className="h-3 w-3 cursor-pointer text-white"
              onClick={() => handleRemoveItem(chip.id)}
            />
          </Badge>
        ))}
      </div>

      <Button
        className={cn(
          "relative text-left justify-between px-3 py-2 rounded-md",
          isFullWidth && "w-full",
        )}
        onClick={() => setModalOpen(true)}
        type="button"
        variant="outline"
      >
        <div className="flex items-center space-x-2">
          <PlusIcon className="w-4 h-4" />
          <span>Select {entityNamePlural}...</span>
        </div>
      </Button>

      <EntityPickerModal<T>
        createEntity={createEntity}
        entityNamePlural={entityNamePlural}
        entityNameSingular={entityNameSingular}
        fetchEntities={fetchEntities}
        initialSelectedEntities={selectedEntities}
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        onConfirm={handleConfirm}
        renderEntityRow={renderEntityRow} // pass down custom row if provided
      />
    </div>
  );
}
