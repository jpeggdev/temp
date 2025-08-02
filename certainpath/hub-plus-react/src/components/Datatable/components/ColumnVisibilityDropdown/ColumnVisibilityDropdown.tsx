import React from "react";
import { Table as ReactTableInstance } from "@tanstack/react-table";
import { ChevronDownIcon } from "@heroicons/react/24/outline";
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/react";

interface ColumnVisibilityDropdownProps<TData extends object> {
  table: ReactTableInstance<TData>;
}

function ColumnVisibilityDropdown<TData extends object>({
  table,
}: ColumnVisibilityDropdownProps<TData>) {
  const handleColumnVisibilityChange = (columnId: string, checked: boolean) => {
    const targetColumn = table.getColumn(columnId);
    targetColumn?.toggleVisibility(checked);
  };

  return (
    <Menu as="div" className="relative">
      <MenuButton className="flex items-center justify-center bg-secondary text-white px-4 py-2 rounded-md dark:bg-primary hover:bg-secondary-dark focus:outline-none">
        <span>Columns</span>
        <ChevronDownIcon className="w-5 h-5 ml-2 -mr-1" />
      </MenuButton>
      <MenuItems className="absolute right-0 z-10 mt-2 w-56 bg-white dark:bg-gray-800 divide-y divide-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
        {table.getAllLeafColumns().map((column) => (
          <MenuItem as="div" key={column.id}>
            {({ active }) => (
              <div
                className={`${active ? "bg-gray-100 dark:bg-gray-700" : ""} px-4 py-2 cursor-pointer`}
                onClick={(e) => {
                  e.preventDefault();
                  handleColumnVisibilityChange(
                    column.id,
                    !column.getIsVisible(),
                  );
                }}
              >
                <div className="flex items-center space-x-2">
                  <input
                    checked={column.getIsVisible()}
                    className="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                    onChange={(e) => {
                      e.stopPropagation();
                      handleColumnVisibilityChange(column.id, e.target.checked);
                    }}
                    onClick={(e) => e.stopPropagation()}
                    type="checkbox"
                  />
                  <span onClick={(e) => e.stopPropagation()}>
                    {column.columnDef.header as string}
                  </span>
                </div>
              </div>
            )}
          </MenuItem>
        ))}
      </MenuItems>
    </Menu>
  );
}

export default ColumnVisibilityDropdown;
