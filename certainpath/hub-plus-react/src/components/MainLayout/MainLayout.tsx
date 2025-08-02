import React, { useState } from "react";
import { Outlet } from "react-router-dom";
import { useSelector } from "react-redux";
import { selectUserAppSettings } from "../../modules/hub/features/UserAppSettings/selectors/userAppSettingsSelectors";
import { SidebarProvider } from "@/components/ui/sidebar";
import WideSidebar from "../WideSidebar/WideSidebar";
import TopNavBar from "../TopNavBar/TopNavBar";
import MainContent from "../MainContent/MainContent";
import AppLegacyBanner from "@/components/AppLegacyBanner/AppLegacyBanner";

interface MainLayoutProps {
  section: string | null;
}

const MainLayout: React.FC<MainLayoutProps> = () => {
  const [sidebarOpenWide, setSidebarOpenWide] = useState(true);
  const userAppSettings = useSelector(selectUserAppSettings);
  const showLegacyBanner = !!userAppSettings?.legacyBannerToggle;
  const isAccountSetupIncomplete = userAppSettings && !userAppSettings.roleName;

  return (
    <>
      <SidebarProvider onOpenChange={setSidebarOpenWide} open={sidebarOpenWide}>
        <WideSidebar />
        <div className="flex flex-1 flex-col min-w-0">
          {showLegacyBanner && <AppLegacyBanner />}
          <TopNavBar
            onSidebarToggle={() => setSidebarOpenWide((prev) => !prev)}
          />
          <MainContent>
            {isAccountSetupIncomplete ? (
              <div> IncompleteAccountSetup goes here</div>
            ) : (
              <Outlet />
            )}
          </MainContent>
        </div>
      </SidebarProvider>
    </>
  );
};

export default MainLayout;
