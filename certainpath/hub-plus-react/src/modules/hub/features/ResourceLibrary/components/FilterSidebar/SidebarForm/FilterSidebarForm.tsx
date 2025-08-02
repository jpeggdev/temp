import React, { useState } from "react";
import { UseFormReturn } from "react-hook-form";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import FilterSidebarFavoriteButton from "@/modules/hub/features/ResourceLibrary/components/FilterSidebar/FavoriteButton/FilterSidebarFavoriteButton";
import { ResourceLibraryMetadataFilters } from "@/modules/hub/features/ResourceLibrary/api/getResourceLibratyMetadata/types";
import { Filter as DefaultFilterIcon } from "lucide-react";
import { FilterSidebarFormData } from "@/modules/hub/features/ResourceLibrary/hooks/FilterSidebarFormSchema";
import { IconSvg } from "@/modules/hub/features/ResourceLibrary/components/FilterSidebar/IconSvg/IconSvg";

interface FilterSidebarFormProps {
  loading?: boolean;
  onSubmit: (values: FilterSidebarFormData) => void;
  form: UseFormReturn<FilterSidebarFormData>;
  filters: ResourceLibraryMetadataFilters | null;
}

const FilterSidebarForm: React.FC<FilterSidebarFormProps> = ({
  loading = false,
  onSubmit,
  form,
  filters,
}) => {
  const {
    handleSubmit,
    control,
    formState: { isSubmitting },
  } = form;

  const [showAllCategories, setShowAllCategories] = useState(false);

  const availableTrades = filters?.trades ?? [];
  const availableContentTypes = filters?.resourceTypes ?? [];
  const availableCategories = filters?.resourceCategories ?? [];

  const visibleCategories = showAllCategories
    ? availableCategories
    : availableCategories.slice(0, 3);

  return (
    <Form {...form}>
      <form className="space-y-6" onSubmit={handleSubmit(onSubmit)}>
        <FormField
          control={control}
          name="searchTerm"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Search</FormLabel>
              <FormControl>
                <Input
                  {...field}
                  disabled={loading || isSubmitting}
                  placeholder="Enter search term"
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="isFavoriteOnly"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Favorite</FormLabel>
              <FormControl>
                <FilterSidebarFavoriteButton
                  onChange={field.onChange}
                  value={field.value}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="contentTypes"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Content Types</FormLabel>
              <FormControl>
                <div className="space-y-2">
                  {availableContentTypes.map((type) => {
                    const isSelected = field.value?.some(
                      (item) => item.id === type.id,
                    );

                    const icon = type.icon ? (
                      <IconSvg
                        className="w-4 h-4 text-gray-700 [&>svg]:w-full [&>svg]:h-full [&>svg]:stroke-current [&>svg]:fill-none"
                        svg={type.icon}
                      />
                    ) : (
                      <DefaultFilterIcon className="w-4 h-4 text-gray-700" />
                    );

                    return (
                      <button
                        className={`flex items-center justify-between w-full px-4 py-2 rounded-xl transition-colors ${
                          isSelected
                            ? "bg-blue-200 hover:bg-blue-300"
                            : "bg-gray-100 hover:bg-gray-200"
                        }`}
                        key={type.id}
                        onClick={() => {
                          const selected = [...(field.value || [])];
                          const index = selected.findIndex(
                            (item) => item.id === type.id,
                          );

                          if (index >= 0) {
                            selected.splice(index, 1);
                          } else {
                            selected.push({
                              id: type.id,
                              name: type.name,
                              resourceCount: type.resourceCount,
                            });
                          }

                          field.onChange(selected);
                        }}
                        type="button"
                      >
                        <div className="flex items-center gap-2">
                          {icon}
                          <span className="text-sm font-medium text-gray-800">
                            {type.name}
                          </span>
                        </div>
                        <span
                          className={`w-5 h-5 flex items-center justify-center text-xs rounded-full font-semibold ${
                            isSelected
                              ? "bg-blue-500 text-white"
                              : "bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                          }`}
                        >
                          {type.resourceCount}
                        </span>
                      </button>
                    );
                  })}
                </div>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="trades"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Trades</FormLabel>
              <FormControl>
                <div className="space-y-2">
                  {availableTrades.map((trade) => {
                    const isSelected = field.value?.some(
                      (item) => item.id === trade.id,
                    );

                    const icon = trade.icon ? (
                      <IconSvg
                        className="w-4 h-4 text-gray-700 [&>svg]:w-full [&>svg]:h-full [&>svg]:stroke-current [&>svg]:fill-none"
                        svg={trade.icon}
                      />
                    ) : (
                      <DefaultFilterIcon className="w-4 h-4 text-gray-700" />
                    );

                    return (
                      <button
                        className={`flex items-center justify-between w-full px-4 py-2 rounded-xl transition-colors ${
                          isSelected
                            ? "bg-blue-200 hover:bg-blue-300"
                            : "bg-gray-100 hover:bg-gray-200"
                        }`}
                        key={trade.id}
                        onClick={() => {
                          const selected = [...(field.value || [])];
                          const index = selected.findIndex(
                            (item) => item.id === trade.id,
                          );

                          if (index >= 0) {
                            selected.splice(index, 1);
                          } else {
                            selected.push({
                              id: trade.id,
                              name: trade.name,
                            });
                          }

                          field.onChange(selected);
                        }}
                        type="button"
                      >
                        <div className="flex items-center gap-2">
                          {icon}
                          <span className="text-sm font-medium text-gray-800">
                            {trade.name}
                          </span>
                        </div>
                      </button>
                    );
                  })}
                </div>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="employeeRoles"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Job Title</FormLabel>
              <FormControl>
                <div className="space-y-2">
                  {(filters?.employeeRoles ?? []).map((role) => {
                    const isSelected = field.value?.some(
                      (item) => item.id === role.id,
                    );

                    return (
                      <label
                        className="flex items-center gap-2 text-sm text-gray-800"
                        key={role.id}
                      >
                        <input
                          checked={isSelected}
                          className="accent-primary h-4 w-4"
                          onChange={(e) => {
                            const { checked } = e.target;
                            const currentValue = [...(field.value || [])];

                            if (checked) {
                              if (!isSelected) {
                                currentValue.push({
                                  id: role.id,
                                  name: role.name,
                                });
                              }
                            } else {
                              const index = currentValue.findIndex(
                                (item) => item.id === role.id,
                              );
                              if (index >= 0) {
                                currentValue.splice(index, 1);
                              }
                            }

                            field.onChange(currentValue);
                          }}
                          type="checkbox"
                        />
                        {role.name}
                      </label>
                    );
                  })}
                </div>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="categories"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Categories</FormLabel>
              <FormControl>
                <>
                  <div className="space-y-2">
                    {visibleCategories.map((cat) => {
                      const isSelected = field.value?.some(
                        (item) => item.id === cat.id,
                      );

                      return (
                        <label
                          className="flex items-center gap-2 text-sm text-gray-800"
                          key={cat.id}
                        >
                          <input
                            checked={isSelected}
                            className="accent-primary h-4 w-4"
                            onChange={(e) => {
                              const { checked } = e.target;
                              const currentValue = [...(field.value || [])];

                              if (checked && !isSelected) {
                                currentValue.push({
                                  id: cat.id,
                                  name: cat.name,
                                });
                              } else if (!checked) {
                                const index = currentValue.findIndex(
                                  (item) => item.id === cat.id,
                                );
                                if (index >= 0) {
                                  currentValue.splice(index, 1);
                                }
                              }

                              field.onChange(currentValue);
                            }}
                            type="checkbox"
                          />
                          {cat.name}
                        </label>
                      );
                    })}
                  </div>

                  {availableCategories.length > 3 && (
                    <div className="mt-2">
                      <button
                        className="text-blue-600 text-sm font-medium hover:underline"
                        onClick={() => setShowAllCategories((prev) => !prev)}
                        type="button"
                      >
                        {showAllCategories ? "View less" : "View more"}
                      </button>
                    </div>
                  )}
                </>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
      </form>
    </Form>
  );
};

export default FilterSidebarForm;
