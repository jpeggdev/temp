import React from "react";

interface CertainPathButtonProps {
  type: "submit" | "button";
  onClick?: () => void;
  disabled?: boolean;
  children: React.ReactNode;
}

const CertainPathButton: React.FC<CertainPathButtonProps> = ({
  type,
  onClick,
  disabled = false,
  children,
}) => {
  return (
    <button
      className={`px-4 py-2 text-white rounded-md focus:outline-none ${
        disabled
          ? "bg-gray-400 cursor-not-allowed"
          : "bg-secondary dark:bg-primary hover:bg-secondary-light dark:hover:bg-primary-light"
      }`}
      disabled={disabled}
      onClick={onClick}
      type={type}
    >
      {children}
    </button>
  );
};

export default CertainPathButton;
