import React, { ChangeEvent } from "react";
import { Search as SearchIcon, X as ClearIcon } from "lucide-react";
import { Input } from "@/components/ui/input";

interface SearchInputProps {
  value: string;
  onChange: (val: string) => void;
  placeholder?: string;
  className?: string;
}

const SearchInput: React.FC<SearchInputProps> = ({
  value,
  onChange,
  placeholder = "Search…",
  className = "",
}) => {
  const handleChange = (e: ChangeEvent<HTMLInputElement>) =>
    onChange(e.target.value);

  const clear = () => onChange("");

  return (
    <div className={`relative w-full ${className}`}>
      <SearchIcon className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
      <Input
        className="pl-8 pr-10"
        onChange={handleChange}
        placeholder={placeholder}
        value={value}
      />
      {value && (
        <button
          aria-label="Clear search"
          className="absolute right-2 top-2.5 h-4 w-4 flex items-center justify-center text-gray-400 hover:text-gray-600"
          onClick={clear}
          type="button"
        >
          <ClearIcon className="h-4 w-4" />
        </button>
      )}
    </div>
  );
};

export default SearchInput;
