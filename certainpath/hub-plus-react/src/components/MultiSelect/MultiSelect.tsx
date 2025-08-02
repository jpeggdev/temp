"use client";

import * as React from "react";
import { Check, ChevronsUpDown, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from "@/components/ui/command";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { Badge } from "@/components/ui/badge";
import { ScrollArea } from "@/components/ui/scroll-area";
import { cn } from "@/components/ui/lib/utils";

export interface MultiSelectProps {
  options: { label: string; value: string }[];
  value: string[];
  onChange: (value: string[]) => void;
  label?: string;
  error?: string;
  placeholder?: string;
  className?: string;
}

export function MultiSelect({
  options,
  value,
  onChange,
  label,
  error,
  placeholder = "Select items...",
  className,
}: MultiSelectProps): JSX.Element {
  const [open, setOpen] = React.useState(false);

  const selectedOptions = options.filter((option) =>
    value.includes(option.value),
  );

  const handleRemoveItem = (itemValue: string, e: React.MouseEvent): void => {
    e.stopPropagation();
    const newValue = value.filter((v) => v !== itemValue);
    onChange(newValue);
  };

  return (
    <div className={cn("space-y-2", className)}>
      {label && (
        <label className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
          {label}
        </label>
      )}

      <Popover onOpenChange={setOpen} open={open}>
        <PopoverTrigger asChild>
          <Button
            aria-expanded={open}
            className={cn(
              "w-full justify-between min-h-[36px]",
              error ? "border-red-500" : "",
              !value.length && "text-muted-foreground",
              "px-3 py-2",
            )}
            role="combobox"
            size="dynamic"
            variant="outline"
          >
            <div className="flex flex-wrap items-center gap-1 max-w-[calc(100%-24px)] overflow-hidden">
              {selectedOptions.length > 0 ? (
                selectedOptions.map((option) => (
                  <Badge
                    className="truncate flex items-center space-x-1"
                    key={option.value}
                  >
                    <span className="text-white">{option.label}</span>
                    <X
                      className="h-3 w-3 cursor-pointer text-white"
                      onClick={(e) => handleRemoveItem(option.value, e)}
                    />
                  </Badge>
                ))
              ) : (
                <span className="truncate">{placeholder}</span>
              )}
            </div>
            <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>

        <PopoverContent align="start" className="w-full p-0 bg-white">
          <Command>
            <CommandInput
              placeholder={`Search ${label?.toLowerCase() || "items"}...`}
            />
            <CommandEmpty>No items found.</CommandEmpty>
            <CommandGroup>
              <ScrollArea className="h-60">
                {options.map((option) => {
                  const isSelected = value.includes(option.value);
                  return (
                    <CommandItem
                      className="flex items-center justify-between"
                      key={option.value}
                      onSelect={() => {
                        const newValue = isSelected
                          ? value.filter((v) => v !== option.value)
                          : [...value, option.value];
                        onChange(newValue);
                      }}
                    >
                      <div className="flex items-center">
                        <Check
                          className={cn(
                            "mr-2 h-4 w-4",
                            isSelected ? "opacity-100" : "opacity-0",
                          )}
                        />
                        <span className="truncate">{option.label}</span>
                      </div>
                      {isSelected && (
                        <X
                          className="h-4 w-4 opacity-70 hover:opacity-100 cursor-pointer"
                          onClick={(e) => {
                            e.stopPropagation();
                            handleRemoveItem(option.value, e);
                          }}
                        />
                      )}
                    </CommandItem>
                  );
                })}
              </ScrollArea>
            </CommandGroup>
          </Command>
        </PopoverContent>
      </Popover>

      {error && <p className="text-sm text-red-500">{error}</p>}
    </div>
  );
}
