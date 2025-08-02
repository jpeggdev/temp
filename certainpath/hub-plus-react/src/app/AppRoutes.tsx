import { Routes, Route, Navigate } from "react-router-dom";
import {
  HubRoutes,
  EventRegistrationRoutes,
  StochasticRoutes,
  AdminRoutes,
  CoachingRoutes,
  EmailManagementRoutes,
} from "./routes";
import { TermsOfService } from "../components/TermsOfService/TermsOfService";
import Logout from "../modules/hub/features/Auth/components/Logout/Logout";
import ForbiddenPage from "@/modules/hub/features/Auth/components/ForbiddenPage/ForbiddenPage";

function AppRoutes() {
  return (
    <Routes>
      <Route element={<Navigate replace to="/hub" />} path="/" />

      <Route
        element={<Navigate replace to="/certain-path/coaches-dashboard" />}
        path="/certain-path"
      />

      <Route
        element={<TermsOfService />}
        path="/certain-path/terms-of-service"
      />

      <Route element={<ForbiddenPage />} path="/403" />

      {HubRoutes}
      {EventRegistrationRoutes}
      {StochasticRoutes}
      {AdminRoutes}
      {CoachingRoutes}
      {EmailManagementRoutes}
      <Route element={<Logout />} path="/logout" />
    </Routes>
  );
}

export default AppRoutes;
