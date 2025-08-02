import React, { useRef, useEffect } from "react";
import { useTheme } from "../../../../../../context/ThemeContext";

interface IconDropdownProps {
  icon: React.ReactNode;
  items: { label: string; action: () => void }[];
  isOpen: boolean;
  onToggle: () => void;
  onClose: () => void;
}

export const IconDropdown: React.FC<IconDropdownProps> = ({
  icon,
  items,
  isOpen,
  onToggle,
  onClose,
}) => {
  const dropdownRef = useRef<HTMLDivElement>(null);
  const { theme } = useTheme();

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        dropdownRef.current &&
        !dropdownRef.current.contains(event.target as Node)
      ) {
        onClose();
      }
    };

    if (isOpen) {
      document.addEventListener("mousedown", handleClickOutside);
    }

    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [isOpen, onClose]);

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        className={`p-1 rounded transition-colors ${
          theme === "dark"
            ? "hover:bg-primary-dark/20 text-gray-300"
            : "hover:bg-primary/10 text-gray-600"
        }`}
        onClick={onToggle}
      >
        {icon}
      </button>

      {isOpen && (
        <div
          className={`absolute left-0 mt-1 w-48 rounded-md shadow-lg z-10 py-1 ${
            theme === "dark"
              ? "bg-secondary text-white"
              : "bg-white text-gray-700"
          }`}
        >
          {items.map((item, index) => (
            <button
              className={`block w-full text-left px-4 py-2 text-sm ${
                theme === "dark"
                  ? "hover:bg-primary-dark/20"
                  : "hover:bg-primary/10"
              }`}
              key={index}
              onClick={() => {
                item.action();
                onClose();
              }}
            >
              {item.label}
            </button>
          ))}
        </div>
      )}
    </div>
  );
};
