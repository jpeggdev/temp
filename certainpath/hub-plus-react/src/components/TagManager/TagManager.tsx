import React, { useState, useEffect, ChangeEvent, KeyboardEvent } from "react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { fetchTags } from "@/api/fetchTags/fetchTagsApi";
import { FetchTagsResponse, Tag } from "@/api/fetchTags/types";

export function sanitizeTag(tag: string) {
  return tag.toLowerCase().replace(/[^a-z0-9_-]/g, "_");
}

interface TagManagerProps {
  existingTags?: string[];
  onTagsChange: (tags: string[]) => void;
  required?: boolean;
  maxTags?: number;
  createNew?: boolean;
}

const TagManager: React.FC<TagManagerProps> = ({
  existingTags = [],
  onTagsChange,
  required = true,
  createNew = true,
  maxTags = 5, // Default maximum number of tags
}) => {
  const [tags, setTags] = useState<string[]>(existingTags);
  const [input, setInput] = useState<string>("");
  const [suggestions, setSuggestions] = useState<Tag[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [error, setError] = useState<string>("");
  const [page] = useState<number>(1);

  useEffect(() => {
    setTags(existingTags);
  }, [existingTags]);

  const handleInputChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    setInput(value);
    setError("");

    if (!value.trim()) {
      setSuggestions([]);
      return;
    }

    setIsLoading(true);
    try {
      const response: FetchTagsResponse = await fetchTags(page, value);
      if (response.data && response.data.tags) {
        setSuggestions(response.data.tags);
      }
    } catch (error) {
      console.error("Error fetching tags:", error);
      setSuggestions([]);
    } finally {
      setIsLoading(false);
    }
  };

  const addTag = (newTag: string) => {
    const normalizedTag = newTag.trim().toLowerCase();

    // Don't add if empty
    if (!normalizedTag) {
      return;
    }

    // Check if we've reached the maximum number of tags
    if (tags.length >= maxTags) {
      setError(`Maximum of ${maxTags} tags allowed`);
      return;
    }

    // Don't add duplicates
    if (tags.includes(normalizedTag)) {
      setError(`Tag "${normalizedTag}" already exists`);
      return;
    }
    const updatedTags = [...tags, normalizedTag];
    setTags(updatedTags);
    onTagsChange(updatedTags);
    setInput("");
    setError("");
    setSuggestions([]);
  };

  const removeTag = (tagToRemove: string) => {
    const updatedTags = tags.filter((tag) => tag !== tagToRemove);
    setTags(updatedTags);
    onTagsChange(updatedTags);

    // Show error if tags are required but none are selected
    if (required && updatedTags.length === 0) {
      setError("At least one tag is required");
    }
  };

  const sanitizeTag = (tag: string): string => {
    return tag.toLowerCase().replace(/[^a-z0-9_-]/g, "_");
  };

  const handleKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" || e.key === "Tab") {
      e.preventDefault();
      if (createNew && input.trim()) {
        addTag(sanitizeTag(input));
      } else if (required && tags.length === 0) {
        setError("At least one tag is required");
      }
    }
  };

  const validateTagsOnBlur = () => {
    if (required && tags.length === 0 && !input.trim()) {
      setError("At least one tag is required");
    }
  };

  return (
    <div className="w-full max-w-2xl">
      <div
        className={`flex flex-wrap gap-2 p-2 border rounded-lg bg-white min-h-12 ${error ? "border-red-500" : ""}`}
      >
        {/* Multiple tags display */}
        {tags.map((tag) => (
          <span
            className="flex items-center gap-1 px-2 py-1 text-sm bg-blue-100 text-blue-800 rounded-full"
            key={tag}
          >
            {tag}
            <button
              aria-label={`Remove tag ${tag}`}
              className="p-0.5 hover:bg-blue-200 rounded-full"
              onClick={() => removeTag(tag)}
              type="button"
            >
              <XMarkIcon aria-hidden="true" className="h-4 w-4" />
            </button>
          </span>
        ))}

        {/* Input field - always shown to allow adding more tags */}
        <input
          aria-invalid={!!error}
          className="flex-grow min-w-24 p-1 outline-none"
          onBlur={validateTagsOnBlur}
          onChange={handleInputChange}
          onKeyDown={handleKeyDown}
          placeholder={
            tags.length > 0
              ? "Add another tag..."
              : required
                ? "Add a tag (required)..."
                : "Add a tag..."
          }
          type="text"
          value={input}
        />
      </div>

      {/* Error message */}
      {error && <p className="mt-1 text-sm text-red-500">{error}</p>}

      {/* Tag count */}
      {maxTags && (
        <p className="mt-1 text-sm text-gray-500">
          {tags.length} of {maxTags} tags used
        </p>
      )}

      {/* Suggestions dropdown */}
      {(suggestions.length > 0 || isLoading) && input && (
        <div className="mt-1 border rounded-lg shadow-lg bg-white">
          {isLoading ? (
            <div className="px-4 py-2 text-gray-500">
              Loading suggestions...
            </div>
          ) : (
            <>
              {suggestions.map((suggestion: Tag) => (
                <button
                  className="w-full px-4 py-2 text-left hover:bg-gray-100 first:rounded-t-lg last:rounded-b-lg"
                  key={suggestion.id}
                  onClick={() => addTag(suggestion.name)}
                  type="button"
                >
                  {suggestion.name}
                </button>
              ))}
              {createNew &&
                input.trim() &&
                !suggestions.some(
                  (s) => s.name.toLowerCase() === input.trim().toLowerCase(),
                ) && (
                  <button
                    className="w-full px-4 py-2 text-left hover:bg-gray-100 text-blue-600 border-t"
                    onClick={() => addTag(sanitizeTag(input))}
                    type="button"
                  >
                    Create new tag: {sanitizeTag(input)}
                  </button>
                )}
            </>
          )}
        </div>
      )}
    </div>
  );
};

export default TagManager;
