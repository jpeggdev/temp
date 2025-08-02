import React from "react";
import FilterSidebarForm from "@/modules/hub/features/ResourceLibrary/components/FilterSidebar/SidebarForm/FilterSidebarForm";
import { UseFormReturn } from "react-hook-form";
import { ResourceLibraryMetadataFilters } from "@/modules/hub/features/ResourceLibrary/api/getResourceLibratyMetadata/types";
import { FilterSidebarFormData } from "@/modules/hub/features/ResourceLibrary/hooks/FilterSidebarFormSchema";
import { useIsMobile } from "@/hooks/use-mobile";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";

interface FilterSidebarProps {
  form: UseFormReturn<FilterSidebarFormData>;
  filters: ResourceLibraryMetadataFilters | null;
  isLoading: boolean;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
}

export const FilterSidebar: React.FC<FilterSidebarProps> = ({
  form,
  filters,
  isLoading,
  isOpen,
  onOpenChange,
}) => {
  const isMobile = useIsMobile();

  if (isMobile) {
    return (
      <Sheet onOpenChange={onOpenChange} open={isOpen}>
        <SheetContent
          className="h-screen p-6 overflow-y-auto"
          side="left"
          style={{ width: "100vw", maxWidth: "100vw" }}
        >
          <SheetHeader>
            <SheetTitle>Filters</SheetTitle>
          </SheetHeader>
          <FilterSidebarForm
            filters={filters}
            form={form}
            loading={isLoading}
            onSubmit={() => {}}
          />
        </SheetContent>
      </Sheet>
    );
  }

  // Desktop view
  return (
    <aside className="w-full h-full overflow-y-auto px-4">
      <h2 className="text-lg font-semibold mb-1">Filters</h2>
      <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Refine your search results
      </p>
      <div className="space-y-3">
        <FilterSidebarForm
          filters={filters}
          form={form}
          loading={isLoading}
          onSubmit={() => {}}
        />
      </div>
    </aside>
  );
};
