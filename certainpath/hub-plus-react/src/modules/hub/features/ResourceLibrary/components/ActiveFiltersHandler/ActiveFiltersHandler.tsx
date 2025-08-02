import React from "react";
import { UseFormReturn } from "react-hook-form";
import { Star } from "lucide-react";
import { FilterSidebarFormData } from "@/modules/hub/features/ResourceLibrary/hooks/FilterSidebarFormSchema";

interface ActiveFiltersHandlerProps {
  form: UseFormReturn<FilterSidebarFormData>;
  onClearAll: () => void;
}

const ActiveFiltersHandler: React.FC<ActiveFiltersHandlerProps> = ({
  form,
  onClearAll,
}) => {
  const searchTerm = form.watch("searchTerm");
  const isFavoriteOnly = form.watch("isFavoriteOnly");
  const trades = form.watch("trades");
  const contentTypes = form.watch("contentTypes");
  const categories = form.watch("categories");
  const employeeRoles = form.watch("employeeRoles");

  const hasActiveFilters =
    searchTerm ||
    isFavoriteOnly ||
    trades.length > 0 ||
    contentTypes.length > 0 ||
    categories.length > 0 ||
    employeeRoles.length > 0;

  if (!hasActiveFilters) return null;

  const handleRemoveContentType = (id: number) => {
    const updated = form
      .getValues("contentTypes")
      .filter((item) => item.id !== id);
    form.setValue("contentTypes", updated);
  };

  const handleRemoveTrade = (id: number) => {
    const updated = form.getValues("trades").filter((item) => item.id !== id);
    form.setValue("trades", updated);
  };

  const handleRemoveCategory = (id: number) => {
    const updated = form
      .getValues("categories")
      .filter((item) => item.id !== id);
    form.setValue("categories", updated);
  };

  const handleRemoveEmployeeRole = (id: number) => {
    const updated = form
      .getValues("employeeRoles")
      .filter((item) => item.id !== id);
    form.setValue("employeeRoles", updated);
  };

  return (
    <div className="mb-6">
      <div className="flex items-center gap-2">
        <span className="text-sm text-gray-500 dark:text-gray-400">
          Active filters:
        </span>
        <div className="flex flex-wrap gap-2">
          {contentTypes.map((ct) => (
            <span
              className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm flex items-center gap-1"
              key={`content-type-${ct.id}`}
            >
              {ct.name}
              <button
                className="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                onClick={() => handleRemoveContentType(ct.id)}
              >
                ×
              </button>
            </span>
          ))}

          {trades.map((trade) => (
            <span
              className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm flex items-center gap-1"
              key={`trade-${trade.id}`}
            >
              {trade.name}
              <button
                className="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                onClick={() => handleRemoveTrade(trade.id)}
              >
                ×
              </button>
            </span>
          ))}

          {categories.map((cat) => (
            <span
              className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm flex items-center gap-1"
              key={`category-${cat.id}`}
            >
              {cat.name}
              <button
                className="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                onClick={() => handleRemoveCategory(cat.id)}
              >
                ×
              </button>
            </span>
          ))}

          {employeeRoles.map((role) => (
            <span
              className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm flex items-center gap-1"
              key={`employee-role-${role.id}`}
            >
              {role.name}
              <button
                className="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                onClick={() => handleRemoveEmployeeRole(role.id)}
              >
                ×
              </button>
            </span>
          ))}

          {isFavoriteOnly && (
            <span className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 px-3 py-1 rounded-full text-sm flex items-center gap-1">
              <Star className="h-3 w-3 fill-current" />
              Favorites only
              <button
                className="ml-1 text-yellow-600 dark:text-yellow-300 hover:text-yellow-800 dark:hover:text-yellow-100"
                onClick={() => form.setValue("isFavoriteOnly", false)}
              >
                ×
              </button>
            </span>
          )}

          {searchTerm && (
            <span className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm flex items-center gap-1">
              {searchTerm}
              <button
                className="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                onClick={() => form.setValue("searchTerm", "")}
              >
                ×
              </button>
            </span>
          )}
        </div>
        <button
          className="ml-2 text-sm text-blue-600 dark:text-blue-400 hover:underline"
          onClick={onClearAll}
        >
          Clear all
        </button>
      </div>
    </div>
  );
};

export default ActiveFiltersHandler;
