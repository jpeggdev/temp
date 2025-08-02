import React from "react";
import { Button } from "@/components/ui/button";

interface ToggleButtonGroupProps {
  activeTab: "edit" | "preview";
  setActiveTab: (tab: "edit" | "preview") => void;
}

export default function ToggleButtonGroup({
  activeTab,
  setActiveTab,
}: ToggleButtonGroupProps) {
  return (
    <div className="flex space-x-2 bg-gray-100 p-1 rounded-lg w-fit mb-1">
      <Button
        className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors
          ${activeTab === "edit" ? "bg-white shadow text-black hover:bg-gray-50" : "text-gray-500 hover:bg-gray-200"}`}
        onClick={() => setActiveTab("edit")}
        type="button"
        variant="ghost"
      >
        Edit HTML
      </Button>

      <Button
        className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors
          ${activeTab === "preview" ? "bg-white shadow text-black hover:bg-gray-50" : "text-gray-500 hover:bg-gray-200"}`}
        onClick={() => setActiveTab("preview")}
        type="button"
        variant="ghost"
      >
        Preview
      </Button>
    </div>
  );
}
