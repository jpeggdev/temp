import React from "react";

interface ClearButtonProps {
  onClick: () => void;
  isVisible: boolean;
}

const ClearButton: React.FC<ClearButtonProps> = ({ onClick, isVisible }) => {
  if (!isVisible) return null;

  return (
    <button
      aria-label="Clear search"
      className="absolute inset-y-0 right-0 flex items-center pr-3"
      onClick={onClick}
      type="button"
    >
      <svg
        aria-hidden="true"
        className="h-5 w-5 text-gray-400 hover:text-gray-600"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M6 18L18 6M6 6l12 12"
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={2}
        />
      </svg>
    </button>
  );
};

export default ClearButton;
