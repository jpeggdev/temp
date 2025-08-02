"use client";

import React, { useState } from "react";
import { Link } from "react-router-dom";
import { useSelector } from "react-redux";
import { NavigationItem } from "@/navigation/types";
import { getUnifiedNavigation } from "@/navigation/unifiedNavigation";
import { useTheme } from "@/context/ThemeContext";
import {
  selectActiveSessionCompanyName,
  selectIntacctId,
} from "@/modules/hub/features/UserAppSettings/selectors/userAppSettingsSelectors";
import logoMain from "@/assets/images/logo-main.svg";
import logoMainWhite from "@/assets/images/logo-main-white.svg";
import {
  SidebarHeader,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
  SidebarMenuSub,
  SidebarSeparator,
} from "@/components/ui/sidebar";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";
import {
  ChevronRightIcon,
  ChevronUpDownIcon,
} from "@heroicons/react/24/outline";
import permissionsService from "@/services/permissionsService";
import { filterNavigationByAccess } from "@/utils/navigationHelpers";
import { RootState } from "@/app/rootReducer";
import CompanySwitcherModal from "@/components/CompanySwitcherModal/CompanySwitcherModal";

interface MobileSideNavProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export default function MobileSideNav({
  open,
  onOpenChange,
}: MobileSideNavProps) {
  const activeCompanyName = useSelector(selectActiveSessionCompanyName);
  const activeCompanyIntacctId = useSelector(selectIntacctId);

  // Pull in permissions
  const userAppSettings = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );
  const permissions = userAppSettings?.permissions ?? [];
  const canOpenCompanySwitcher =
    permissions.includes("CAN_SWITCH_COMPANY_ALL") ||
    permissions.includes("CAN_SWITCH_COMPANY_MARKETING_ONLY");

  // Theme / navigation
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

  // State for menu toggling & submenus
  const [openItems, setOpenItems] = useState<Record<string, boolean>>({});

  // State for company switcher modal
  const [companySwitcherOpen, setCompanySwitcherOpen] = useState(false);

  function handleToggleItem(itemKey: string) {
    setOpenItems((prev) => ({ ...prev, [itemKey]: !prev[itemKey] }));
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
            <Link onClick={() => onOpenChange(false)} to={item.href}>
              <item.icon className="h-4 w-4 shrink-0" />
              <span>{item.name}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      );
    });
  }

  return (
    <>
      <Sheet onOpenChange={onOpenChange} open={open}>
        <SheetContent
          className="h-screen w-screen p-0 m-0 bg-sidebar text-sidebar-foreground overflow-auto"
          side="top"
        >
          <SheetHeader className="sr-only">
            <SheetTitle>Mobile Menu</SheetTitle>
            <SheetDescription>Slides in from the top</SheetDescription>
          </SheetHeader>

          <SidebarHeader className="border-b border-sidebar-border">
            <div className="flex flex-col items-start space-y-3 px-2 py-2">
              <img
                alt="Your Company"
                className="h-8 w-auto"
                src={theme === "dark" ? logoMainWhite : logoMain}
              />
              {canOpenCompanySwitcher ? (
                <button
                  className="flex items-center justify-between w-full text-left"
                  onClick={() => {
                    setCompanySwitcherOpen(true);
                    onOpenChange(false);
                  }}
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
                <div className="flex flex-start flex-col">
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

          <SidebarSeparator />

          <SidebarFooter className="border-t border-sidebar-border">
            <div className="px-2">
              <span className="text-xs text-muted-foreground">
                &copy; {new Date().getFullYear()} CertainPath, Inc. All rights
                reserved.
              </span>
            </div>
          </SidebarFooter>
        </SheetContent>
      </Sheet>

      {/* Company Switcher Modal */}
      <CompanySwitcherModal
        onClose={() => setCompanySwitcherOpen(false)}
        open={companySwitcherOpen}
      />
    </>
  );
}
