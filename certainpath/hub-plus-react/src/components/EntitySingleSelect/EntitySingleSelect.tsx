"use client";

import React, { useState, useEffect } from "react";
import { cn } from "@/components/ui/lib/utils";
import { ChevronsUpDown } from "lucide-react";
import SingleEntityPickerModal, {
  BaseEntity,
  CreateEntityFn,
  FetchEntitiesFn,
} from "@/components/SingleEntityPickerModal/SingleEntityPickerModal";

interface EntitySingleSelectProps<T extends BaseEntity> {
  value: T | null;
  onChange: (newValue: T | null) => void;
  fetchEntities: FetchEntitiesFn<T>;
  createEntity?: CreateEntityFn<T>;
  entityNameSingular?: string;
  entityNamePlural?: string;
  disabled?: boolean;
  disabledMessage?: string;
  renderEntityRow?: (
    entity: T,
    isSelected: boolean,
    toggle: (ent: T) => void,
  ) => React.ReactNode;
  placeholder?: string;
}

export function EntitySingleSelect<T extends BaseEntity>({
  value,
  onChange,
  fetchEntities,
  createEntity,
  entityNameSingular,
  entityNamePlural,
  disabled = false,
  disabledMessage,
  renderEntityRow,
  placeholder = "Please Select",
}: EntitySingleSelectProps<T>) {
  const [selectedEntity, setSelectedEntity] = useState<T | null>(value);
  const [modalOpen, setModalOpen] = useState(false);

  useEffect(() => {
    setSelectedEntity(value);
  }, [value]);

  const handleConfirm = (newSelectedEntity: T | null) => {
    onChange(newSelectedEntity);
    setModalOpen(false);
  };

  return (
    <div className="space-y-1 cursor-pointer">
      <div
        className={cn(
          "flex h-9 w-full items-center justify-between rounded-md border border-input px-3 py-1 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring md:text-sm",
          disabled ? "bg-gray-200 cursor-not-allowed" : "bg-transparent",
        )}
        onClick={() => {
          if (!disabled) {
            setModalOpen(true);
          }
        }}
      >
        <span
          className={cn("w-full", {
            "text-gray-500": disabled,
            "text-gray-400": !selectedEntity && !disabled,
            "text-foreground": selectedEntity && !disabled,
          })}
          title={disabled ? disabledMessage : ""}
        >
          {disabled && disabledMessage ? (
            <>
              <span className="block sm:hidden whitespace-nowrap overflow-hidden text-ellipsis text-base">
                {disabledMessage.length > 30
                  ? `${disabledMessage.slice(0, 30)}...`
                  : disabledMessage}
              </span>
              <span className="hidden sm:block whitespace-nowrap overflow-hidden text-ellipsis text-sm">
                {disabledMessage}
              </span>
            </>
          ) : selectedEntity ? (
            selectedEntity.name
          ) : (
            placeholder
          )}
        </span>
        <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
      </div>

      <SingleEntityPickerModal<T>
        createEntity={createEntity}
        entityNamePlural={entityNamePlural}
        entityNameSingular={entityNameSingular}
        fetchEntities={fetchEntities}
        initialSelectedEntity={selectedEntity}
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        onConfirm={handleConfirm}
        renderEntityRow={renderEntityRow}
      />
    </div>
  );
}
