import React from "react";

interface DashboardTabSelectorProps {
  activeTab: "sales" | "customers";
  setActiveTab: (tab: "sales" | "customers") => void;
}

export const DashboardTabSelector: React.FC<DashboardTabSelectorProps> = ({
  activeTab,
  setActiveTab,
}) => {
  return (
    <div className="relative bg-gray-100 rounded-lg w-full mb-6 p-1 select-none flex gap-x-2">
      {/* Sliding background indicator */}
      <div
        className="absolute top-1 left-1 h-[calc(100%-0.5rem)] rounded-lg bg-white shadow transition-transform duration-300 ease-in-out z-0"
        style={{
          pointerEvents: "none",
          width: "calc(50% - 0.5rem)",
          transform:
            activeTab === "customers"
              ? "translateX(calc(100% + 0.5rem))"
              : "translateX(0)",
        }}
      />

      <button
        className={`relative flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors text-center
          ${
            activeTab === "sales"
              ? "text-black cursor-default"
              : "text-gray-500 hover:bg-gray-200 cursor-pointer"
          }`}
        disabled={activeTab === "sales"}
        onClick={() => setActiveTab("sales")}
        type="button"
      >
        Sales
      </button>

      <button
        className={`relative flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors text-center
          ${
            activeTab === "customers"
              ? "text-black cursor-default"
              : "text-gray-500 hover:bg-gray-200 cursor-pointer"
          }`}
        disabled={activeTab === "customers"}
        onClick={() => setActiveTab("customers")}
        type="button"
      >
        Customers
      </button>
    </div>
  );
};
