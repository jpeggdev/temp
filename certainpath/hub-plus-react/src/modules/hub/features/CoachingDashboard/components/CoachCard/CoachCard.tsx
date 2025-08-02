import React, { useState } from "react";
import { useTheme } from "../../../../../../context/ThemeContext";
import { IconDropdown } from "../IconDropdown/IconDropdown";
import { MetricCard } from "../MetricCard/MetricCard";
import {
  PhoneIcon,
  EnvelopeIcon,
  MapPinIcon,
  DocumentTextIcon,
  BellIcon,
  Cog6ToothIcon,
} from "@heroicons/react/24/outline";
import { CoachMetrics } from "../../data/CoachingDashboardMockdata";

interface CoachCardProps {
  coach: CoachMetrics;
}

const CoachCard: React.FC<CoachCardProps> = ({ coach }) => {
  const { theme } = useTheme();
  const [openDropdown, setOpenDropdown] = useState<string | null>(null);

  const handleDocumentAction = (action: string) => {
    console.log(`Document action: ${action} for ${coach.companyName}`);
  };

  const handleNotificationAction = (action: string) => {
    console.log(`Notification action: ${action} for ${coach.companyName}`);
  };

  const handleSettingsAction = (action: string) => {
    console.log(`Settings action: ${action} for ${coach.companyName}`);
  };

  const documentItems = [
    { label: "View Documents", action: () => handleDocumentAction("view") },
    { label: "Upload Document", action: () => handleDocumentAction("upload") },
    {
      label: "Request Document",
      action: () => handleDocumentAction("request"),
    },
  ];

  const notificationItems = [
    {
      label: "View Notifications",
      action: () => handleNotificationAction("view"),
    },
    {
      label: "Send Notification",
      action: () => handleNotificationAction("send"),
    },
    {
      label: "Notification Settings",
      action: () => handleNotificationAction("settings"),
    },
  ];

  const settingsItems = [
    {
      label: "Account Settings",
      action: () => handleSettingsAction("account"),
    },
    { label: "Preferences", action: () => handleSettingsAction("preferences") },
    { label: "Permissions", action: () => handleSettingsAction("permissions") },
  ];

  return (
    <div
      className={`rounded-lg p-2 md:p-4 mb-4 shadow-sm ${theme === "dark" ? "bg-secondary" : "bg-white"}`}
    >
      <div className="flex flex-col md:flex-row">
        <div
          className={`w-full md:w-1/4 p-3 md:p-4 rounded mb-4 md:mb-0 ${theme === "dark" ? "bg-secondary-dark" : "bg-blue-50"}`}
        >
          <div className="flex space-x-2 mb-4">
            <IconDropdown
              icon={<DocumentTextIcon className="h-5 w-5" />}
              isOpen={openDropdown === "document"}
              items={documentItems}
              onClose={() => setOpenDropdown(null)}
              onToggle={() =>
                setOpenDropdown(openDropdown === "document" ? null : "document")
              }
            />
            <IconDropdown
              icon={<BellIcon className="h-5 w-5" />}
              isOpen={openDropdown === "notification"}
              items={notificationItems}
              onClose={() => setOpenDropdown(null)}
              onToggle={() =>
                setOpenDropdown(
                  openDropdown === "notification" ? null : "notification",
                )
              }
            />
            <IconDropdown
              icon={<Cog6ToothIcon className="h-5 w-5" />}
              isOpen={openDropdown === "settings"}
              items={settingsItems}
              onClose={() => setOpenDropdown(null)}
              onToggle={() =>
                setOpenDropdown(openDropdown === "settings" ? null : "settings")
              }
            />
          </div>
          <h3
            className={`font-bold text-lg mb-2 ${theme === "dark" ? "text-white" : "text-gray-900"}`}
          >
            {coach.companyName}
          </h3>
          <p
            className={`mb-1 ${theme === "dark" ? "text-gray-300" : "text-gray-700"}`}
          >
            {coach.contactName}
          </p>
          <div className="flex items-center text-gray-600 mb-1 cursor-pointer hover:text-blue-600">
            <MapPinIcon className="h-5 w-5 mr-2 flex-shrink-0" />
            <span className="break-words">{coach.location}</span>
          </div>
          <div className="flex items-center text-gray-600 mb-1 cursor-pointer hover:text-blue-600">
            <PhoneIcon className="h-5 w-5 mr-2 flex-shrink-0" />
            <span>{coach.phone}</span>
          </div>
          <div className="flex items-center text-gray-600 mb-1 cursor-pointer hover:text-blue-600 w-full group relative">
            <EnvelopeIcon className="h-5 w-5 mr-2 flex-shrink-0" />
            <span className="truncate">{coach.email}</span>
            <div
              className={`absolute left-0 -bottom-8 hidden group-hover:block px-2 py-1 rounded text-sm z-20 whitespace-nowrap ${
                theme === "dark"
                  ? "bg-secondary-light text-white"
                  : "bg-gray-800 text-white"
              }`}
            >
              {coach.email}
            </div>
          </div>
          <p className="text-gray-600 mt-2">
            Member since: {coach.memberSince}
          </p>
        </div>
        <div className="flex-1 grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 md:pl-4">
          <MetricCard
            label="Club Member Conversion"
            trend={coach.metrics.clubMemberConversion.trend}
            type="percentage"
            value={coach.metrics.clubMemberConversion.value}
          />
          <MetricCard
            label="Gross Margin"
            trend={coach.metrics.grossMargin.trend}
            type="percentage"
            value={coach.metrics.grossMargin.value}
          />
          <MetricCard
            label="Revenue per employee"
            trend={coach.metrics.revenuePerEmployee.trend}
            type="money"
            value={coach.metrics.revenuePerEmployee.value}
          />
          <MetricCard
            label="CSR Booked Ratio"
            trend={coach.metrics.csrBookedRatio.trend}
            type="percentage"
            value={coach.metrics.csrBookedRatio.value}
          />
          <MetricCard
            label="Technician Field Closing Ratio"
            trend={coach.metrics.technicianClosingRatio.trend}
            type="percentage"
            value={coach.metrics.technicianClosingRatio.value}
          />
          <MetricCard
            label="Average Invoice"
            trend={coach.metrics.averageInvoice.trend}
            type="money"
            value={coach.metrics.averageInvoice.value}
          />
          <MetricCard
            label="Call Center Score"
            trend={coach.metrics.callCenterScore.trend}
            type="score"
            value={coach.metrics.callCenterScore.value}
          />
          <MetricCard
            label="Replacement closing rate"
            trend={coach.metrics.replacementClosingRate.trend}
            type="percentage"
            value={coach.metrics.replacementClosingRate.value}
          />
        </div>
      </div>
    </div>
  );
};

export default CoachCard;
