import React, { useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import { Link } from "react-router-dom";
import { RootState } from "@/app/rootReducer";
import {
  selectActiveSessionCompanyName,
  selectIntacctId,
} from "@/modules/hub/features/UserAppSettings/selectors/userAppSettingsSelectors";
import { useTheme } from "@/context/ThemeContext";
import logoMain from "@/assets/images/logo-main.svg";
import logoMainWhite from "@/assets/images/logo-main-white.svg";
import { getUnifiedNavigation } from "@/navigation/unifiedNavigation";
import { NavigationItem } from "@/navigation/types";
import {
  ChevronRightIcon,
  ChevronUpDownIcon,
} from "@heroicons/react/24/outline";
import {
  Sidebar,
  SidebarHeader,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
  SidebarMenuSub,
} from "@/components/ui/sidebar";
import permissionsService from "@/services/permissionsService";
import { filterNavigationByAccess } from "@/utils/navigationHelpers";
import CompanySwitcherModal from "@/components/CompanySwitcherModal/CompanySwitcherModal";
import { toggleWideSidebarItem } from "@/app/globalSlices/wideSidebarSlice";

export default function WideSidebar() {
  const dispatch = useDispatch();
  const openItems = useSelector(
    (state: RootState) => state.wideSidebar.openItems,
  );

  const activeCompanyName = useSelector(selectActiveSessionCompanyName);
  const activeCompanyIntacctId = useSelector(selectIntacctId);
  const userAppSettings = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );

  const { theme } = useTheme();

  const rawNav = getUnifiedNavigation();
  const { hasPermission, hasRole, hasCertainPathCompany } =
    permissionsService();
  const filteredNav = filterNavigationByAccess(
    rawNav,
    hasPermission,
    hasRole,
    hasCertainPathCompany(),
  );

  const [companySwitcherOpen, setCompanySwitcherOpen] = useState(false);

  const permissions = userAppSettings?.permissions ?? [];
  const canOpenCompanySwitcher =
    permissions.includes("CAN_SWITCH_COMPANY_ALL") ||
    permissions.includes("CAN_SWITCH_COMPANY_MARKETING_ONLY");

  function handleToggleItem(itemKey: string) {
    dispatch(toggleWideSidebarItem(itemKey));
  }

  function renderNavigation(items: NavigationItem[]) {
    return items.map((item) => {
      const itemKey = item.internalName || item.name;
      const hasChildren = item.children && item.children.length > 0;
      const isOpen = !!openItems[itemKey];

      if (hasChildren) {
        return (
          <SidebarMenuItem
            data-state={isOpen ? "open" : undefined}
            key={itemKey}
          >
            <SidebarMenuButton
              data-state={isOpen ? "open" : undefined}
              onClick={() => handleToggleItem(itemKey)}
            >
              <item.icon className="h-4 w-4 shrink-0" />
              <span>{item.name}</span>
              <ChevronRightIcon
                aria-hidden="true"
                className={`ml-auto h-4 w-4 transition-transform ${
                  isOpen ? "rotate-90" : ""
                }`}
              />
            </SidebarMenuButton>
            {isOpen && (
              <SidebarMenuSub>
                {renderNavigation(item.children!)}
              </SidebarMenuSub>
            )}
          </SidebarMenuItem>
        );
      }

      return (
        <SidebarMenuItem key={itemKey}>
          <SidebarMenuButton asChild>
            <Link title={item.name} to={item.href ?? "#"}>
              <item.icon className="h-4 w-4 shrink-0" />
              <span>{item.name}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      );
    });
  }

  return (
    <div className="flex-shrink-0 h-screen">
      <Sidebar
        className="h-full"
        collapsible="offcanvas"
        side="left"
        variant="sidebar"
      >
        <SidebarHeader className="border-b border-sidebar-border">
          <div className="flex flex-col space-y-3 px-2 py-2">
            <img
              alt="Your Company"
              className="h-8 w-auto"
              src={theme === "dark" ? logoMainWhite : logoMain}
            />
            {canOpenCompanySwitcher ? (
              <button
                className="flex w-full items-center justify-between text-left"
                onClick={() => setCompanySwitcherOpen(true)}
                type="button"
              >
                <div>
                  <div className="text-sm font-medium">
                    {activeCompanyName || "No Company"}
                  </div>
                  {activeCompanyIntacctId && (
                    <div className="text-xs text-muted-foreground">
                      Intacct ID: {activeCompanyIntacctId}
                    </div>
                  )}
                </div>
                <ChevronUpDownIcon aria-hidden="true" className="h-5 w-5" />
              </button>
            ) : (
              <div className="flex flex-col items-start">
                <div className="text-sm font-medium">
                  {activeCompanyName || "No Company"}
                </div>
                {activeCompanyIntacctId && (
                  <div className="text-xs text-muted-foreground">
                    Intacct ID: {activeCompanyIntacctId}
                  </div>
                )}
              </div>
            )}
          </div>
        </SidebarHeader>

        <SidebarContent>
          <SidebarGroup>
            <SidebarGroupContent>
              <SidebarMenu>{renderNavigation(filteredNav)}</SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        </SidebarContent>
      </Sidebar>

      <CompanySwitcherModal
        onClose={() => setCompanySwitcherOpen(false)}
        open={companySwitcherOpen}
      />
    </div>
  );
}
