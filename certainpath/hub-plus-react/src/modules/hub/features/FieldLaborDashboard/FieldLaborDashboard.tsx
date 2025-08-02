import React from "react";
import MainPageWrapper from "../../../../components/MainPageWrapper/MainPageWrapper";
import BarChart from "../../../../components/BarChart/BarChart";

const FieldLaborDashboard: React.FC = () => {
  const mockData = [
    { name: "Jan", value: 80 },
    { name: "Feb", value: 59 },
    { name: "Mar", value: 60 },
    { name: "Apr", value: 65 },
    { name: "May", value: 74 },
    { name: "Jun", value: 55 },
  ];

  const chartConfigs = [
    { title: "Field Labor %", dataKey: "value", fill: "#8884d8" },
    { title: "Field Closing Ratio", dataKey: "value", fill: "#82ca9d" },
    { title: "Sales Closing Ratio", dataKey: "value", fill: "#ffc658" },
    { title: "Call Center Closing Ratio", dataKey: "value", fill: "#ff7300" },
    { title: "Opportunity Conversion", dataKey: "value", fill: "#0088fe" },
    { title: "Calls Booked", dataKey: "value", fill: "#00C49F" },
  ];

  return (
    <MainPageWrapper error={null} loading={false} title="Field Labor Dashboard">
      <div style={{ display: "flex", flexWrap: "wrap", gap: "20px" }}>
        {chartConfigs.map((config, index) => (
          <div
            key={index}
            style={{ width: "calc(50% - 10px)", minWidth: "300px" }}
          >
            <h3>{config.title}</h3>
            <BarChart
              bars={[{ dataKey: config.dataKey, fill: config.fill }]}
              data={mockData}
              height={300}
              referenceLine={70}
            />
          </div>
        ))}
      </div>
    </MainPageWrapper>
  );
};

export default FieldLaborDashboard;
