import { Route } from "react-router-dom";
import CoachingDashboard from "@/modules/hub/features/CoachingDashboard/components/CoachingDasboard/CoachingDashboard";
import MainLayout from "@/components/MainLayout/MainLayout";
import { AuthenticationGuard } from "@/components/AuthenticationGuard/AuthenticationGuard";

const CoachingRoutes = (
  <Route
    element={
      <AuthenticationGuard
        component={() => <MainLayout section="coaching" />}
      />
    }
    path="/coaching"
  >
    <Route element={<CoachingDashboard />} index />
  </Route>
);

export default CoachingRoutes;
