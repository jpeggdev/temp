import React from "react";
import MainPageWrapper from "../../../../components/MainPageWrapper/MainPageWrapper";

const HubDashboard: React.FC = () => {
  return (
    <MainPageWrapper error={null} loading={false} title="Hub Dashboard">
      <div>
        <h1>Welcome to the Hub Dashboard</h1>
      </div>
    </MainPageWrapper>
  );
};

export default HubDashboard;
