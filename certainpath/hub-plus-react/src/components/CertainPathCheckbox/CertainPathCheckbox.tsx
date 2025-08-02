import React from "react";

interface CertainPathCheckboxProps {
  name: string;
  checked: boolean;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  label: string;
}

const CertainPathCheckbox: React.FC<CertainPathCheckboxProps> = ({
  name,
  checked,
  onChange,
  label,
}) => {
  return (
    <div className="flex items-center">
      <input
        checked={checked}
        className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
        id={name}
        name={name}
        onChange={onChange} // Pass the event directly
        type="checkbox"
      />
      <label className="ml-2 block text-sm text-gray-900" htmlFor={name}>
        {label}
      </label>
    </div>
  );
};

export default CertainPathCheckbox;
