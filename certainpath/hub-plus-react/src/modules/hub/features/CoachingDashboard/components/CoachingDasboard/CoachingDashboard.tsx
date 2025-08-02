import React from "react";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { coachingDashboardMockData } from "../../data/CoachingDashboardMockdata";
import { useTheme } from "../../../../../../context/ThemeContext";
import CoachCard from "../CoachCard/CoachCard";

const CoachingDashboard: React.FC = () => {
  const { theme } = useTheme();
  const mockData = coachingDashboardMockData;

  return (
    <MainPageWrapper error={null} loading={false} title="Coaching Dashboard">
      <div
        className={`min-h-screen p-2 md:p-4 ${theme === "dark" ? "bg-secondary-dark" : "bg-gray-50"}`}
      >
        <div className="text-red-600 font-bold mb-2 text-center">
          This feature is in development
        </div>
        {mockData.map((coach, index) => (
          <CoachCard coach={coach} key={index} />
        ))}
      </div>
    </MainPageWrapper>
  );
};

export default CoachingDashboard;
