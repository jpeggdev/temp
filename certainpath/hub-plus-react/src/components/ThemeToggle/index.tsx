import React from "react";
import { MoonIcon, SunIcon } from "@heroicons/react/24/outline";
import { useTheme } from "../../context/ThemeContext";

const ThemeToggle: React.FC = () => {
  const { theme, toggleTheme } = useTheme();

  return (
    <button
      className="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500"
      onClick={toggleTheme}
      type="button"
    >
      <span className="sr-only">Toggle theme</span>
      {theme === "dark" ? (
        <SunIcon aria-hidden="true" className="h-6 w-6" />
      ) : (
        <MoonIcon aria-hidden="true" className="h-6 w-6" />
      )}
    </button>
  );
};

export default ThemeToggle;
