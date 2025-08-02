"use client";

import { useState } from "react";
import { Button } from "@/components/Button/Button";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/Popover/Popover";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from "@/components/Command/Command";
import { Check, ChevronsUpDown } from "lucide-react";
import { cn } from "@/utils/utils";

export type MultiSelectProps = {
  onChange: (selected: string[]) => void;
  options: string[];
  placeholder: string;
  selected: string[];
};

export function MultiSelect({
  options,
  selected = [],
  onChange,
  placeholder,
}: MultiSelectProps) {
  const [open, setOpen] = useState(false);
  const safeSelected = Array.isArray(selected) ? selected : [];

  return (
    <Popover onOpenChange={setOpen} open={open}>
      <PopoverTrigger asChild>
        <Button
          aria-expanded={open}
          className="w-[200px] justify-between bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600"
          role="combobox"
          variant="outline"
        >
          {safeSelected.length > 0
            ? `${safeSelected.length} selected`
            : placeholder}
          <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-[200px] p-0 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 max-h-80 overflow-y-auto">
        <Command className="bg-transparent" shouldFilter={true}>
          <CommandInput
            className="text-gray-900 dark:text-gray-100"
            placeholder={`Search ${placeholder.toLowerCase()}...`}
          />
          <CommandEmpty className="text-gray-500 dark:text-gray-400">
            No {placeholder.toLowerCase()} found.
          </CommandEmpty>
          <CommandGroup>
            {options.map((option) => (
              <CommandItem
                className="text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                key={option}
                onSelect={() => {
                  const newSelected = safeSelected.includes(option)
                    ? safeSelected.filter((item) => item !== option)
                    : [...safeSelected, option];
                  onChange(newSelected);
                }}
                value={option}
              >
                <Check
                  className={cn(
                    "mr-2 h-4 w-4",
                    safeSelected.includes(option) ? "opacity-100" : "opacity-0",
                  )}
                />
                {option}
              </CommandItem>
            ))}
          </CommandGroup>
        </Command>
      </PopoverContent>
    </Popover>
  );
}
