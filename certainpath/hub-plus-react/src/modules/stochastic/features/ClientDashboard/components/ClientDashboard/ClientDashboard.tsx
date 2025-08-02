import React from "react";
import { Helmet } from "react-helmet-async";
import FieldServicesDashboard from "../../../../../../components/Dashtail/FieldServicesDashboard";

const ClientDashboard: React.FC = () => {
  return (
    <div>
      <Helmet>
        <title>Client Dashboard | Stochastic | Certain Path</title>
      </Helmet>
      <h1>Client Dashboard</h1>
      <FieldServicesDashboard />
    </div>
  );
};

export default ClientDashboard;
