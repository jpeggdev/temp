import React, { useState } from "react";
import { EyeIcon, EyeSlashIcon } from "@heroicons/react/24/outline";

interface CertainPathTextInputProps {
  name: string;
  value: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  placeholder: string;
  error?: string | null;
  type?: string;
}

const CertainPathTextInput: React.FC<CertainPathTextInputProps> = ({
  name,
  value,
  onChange,
  placeholder,
  error,
  type = "text",
}) => {
  const [isPasswordVisible, setPasswordVisible] = useState(false);

  const togglePasswordVisibility = () => {
    setPasswordVisible(!isPasswordVisible);
  };

  const inputType = type === "password" && isPasswordVisible ? "text" : type;

  return (
    <div className="relative">
      <label className="block text-sm font-medium text-gray-700">
        {placeholder}
      </label>
      <div className="relative flex items-center">
        <input
          className={`mt-1 block w-full px-3 py-2 pr-10 border ${
            error
              ? "border-red-500 focus:ring-red-500 focus:border-red-500"
              : "border-gray-300 focus:ring-secondary focus:border-secondary dark:focus:ring-primary dark:focus:border-primary"
          } rounded-md shadow-sm`}
          name={name}
          onChange={onChange}
          placeholder={placeholder}
          type={inputType}
          value={value}
        />
        {type === "password" && (
          <button
            className="absolute right-3 top-1/2 transform -translate-y-1/2"
            onClick={togglePasswordVisibility}
            type="button"
          >
            {isPasswordVisible ? (
              <EyeSlashIcon className="h-5 w-5 text-gray-500" />
            ) : (
              <EyeIcon className="h-5 w-5 text-gray-500" />
            )}
          </button>
        )}
      </div>
      {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
    </div>
  );
};

export default CertainPathTextInput;
