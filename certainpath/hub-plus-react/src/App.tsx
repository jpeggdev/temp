// App.tsx (excerpt)
import React, { useEffect } from "react";
import "./App.css";
import AppRoutes from "./app/AppRoutes";
import useSessionRewind from "./hooks/useSessionRewind";
import { useAuth0 } from "@auth0/auth0-react";
import {
  addAccessTokenInterceptor,
  addCompanyUuidInterceptor,
  addImpersonateUserInterceptor,
} from "./api/axiosInstance";
import { useDispatch, useSelector } from "react-redux";
import { fetchUserAppSettingsAction } from "./modules/hub/features/UserAppSettings/slices/userAppSettingsSlice";
import { ThemeProvider } from "./context/ThemeContext";
import { useNotification } from "./context/NotificationContext";
import axiosInstance from "./api/axiosInstance";
import { Toaster } from "@/components/ui/toaster";
import type { RootState } from "./app/rootReducer";

import AppLoadingPage from "./components/AppLoadingPage/AppLoadingPage";
import LoadFailurePage from "./components/LoadFailurePage/LoadFailurePage";

function App() {
  useSessionRewind();
  const dispatch = useDispatch();

  const {
    isLoading: authLoading,
    getAccessTokenSilently,
    isAuthenticated,
    loginWithRedirect,
  } = useAuth0();

  const { showNotification } = useNotification();

  useEffect(() => {
    addAccessTokenInterceptor(
      axiosInstance,
      getAccessTokenSilently,
      loginWithRedirect,
      showNotification,
    );
    addCompanyUuidInterceptor(axiosInstance, getAccessTokenSilently);
    addImpersonateUserInterceptor(axiosInstance);
  }, [getAccessTokenSilently, loginWithRedirect, showNotification]);

  useEffect(() => {
    if (isAuthenticated) {
      dispatch(fetchUserAppSettingsAction(true));
    }
  }, [isAuthenticated, dispatch]);

  const { userAppSettings, loading: userAppSettingsLoading } = useSelector(
    (state: RootState) => state.userAppSettings,
  );

  if (authLoading || (isAuthenticated && userAppSettingsLoading)) {
    return <AppLoadingPage />;
  }

  if (isAuthenticated && !userAppSettings && !userAppSettingsLoading) {
    return <LoadFailurePage />;
  }

  return (
    <ThemeProvider>
      <AppRoutes />
      <Toaster />
    </ThemeProvider>
  );
}

export default App;
