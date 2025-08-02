"use client";

import React, { useState, useEffect } from "react";
import { Bars3Icon, ChevronDownIcon } from "@heroicons/react/20/solid";
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/react";
import { useSelector } from "react-redux";
import { RootState } from "../../app/rootReducer";
import ThemeToggle from "../ThemeToggle";
import clsx from "clsx";
import { useNavigate, Link } from "react-router-dom";
import MobileSideNav from "@/components/MobileSideNav/MobileSideNav";

function useIsMobile() {
  const [isMobile, setIsMobile] = React.useState(false);
  React.useEffect(() => {
    const handleResize = () => setIsMobile(window.innerWidth < 768);
    handleResize();
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);
  return isMobile;
}

interface TopNavBarProps {
  onSidebarToggle: () => void;
}

const TopNavBar: React.FC<TopNavBarProps> = ({ onSidebarToggle }) => {
  const navigate = useNavigate();
  const isMobile = useIsMobile();
  const userAppSettings = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );
  const fullName = userAppSettings
    ? `${userAppSettings.firstName} ${userAppSettings.lastName}`
    : "Your Name";
  const initials = fullName
    .split(" ")
    .map((n) => n[0])
    .join("");
  const [isImpersonating, setIsImpersonating] = useState(false);

  const [mobileNavOpen, setMobileNavOpen] = useState(false);

  useEffect(() => {
    const impersonateUserUuid = localStorage.getItem("impersonateUserUuid");
    setIsImpersonating(!!impersonateUserUuid);
  }, []);

  const handleExitImpersonation = () => {
    localStorage.removeItem("selectedCompanyUuid");
    localStorage.removeItem("impersonateUserUuid");
    navigate(0);
  };

  const handleHamburgerClick = () => {
    if (isMobile) {
      setMobileNavOpen(true);
    } else {
      onSidebarToggle();
    }
  };

  return (
    <>
      <div className="sticky top-0 z-10 flex h-16 shrink-0 items-center border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
        <div className="flex items-center gap-x-4">
          <button
            className="-m-2.5 p-2.5 text-gray-700"
            onClick={handleHamburgerClick}
            type="button"
          >
            <span className="sr-only">Open menu</span>
            <Bars3Icon aria-hidden="true" className="h-6 w-6" />
          </button>
        </div>
        <div className="flex flex-1 justify-end items-center gap-x-4 lg:gap-x-6">
          <ThemeToggle />
          <Menu as="div" className="relative">
            <MenuButton className="-m-1.5 flex items-center p-1.5">
              <span className="sr-only">Open user menu</span>
              <span className="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-500">
                <span className="text-sm font-medium leading-none text-white">
                  {initials}
                </span>
              </span>
              <span className="hidden lg:flex lg:items-center">
                <span
                  aria-hidden="true"
                  className="ml-4 text-sm font-semibold leading-6 text-gray-900"
                >
                  {fullName}
                </span>
                <ChevronDownIcon
                  aria-hidden="true"
                  className="ml-2 h-5 w-5 text-gray-400"
                />
              </span>
            </MenuButton>
            <MenuItems className="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 transition focus:outline-none">
              <MenuItem>
                {({ active }) => (
                  <Link
                    className={clsx(
                      active ? "bg-gray-50" : "",
                      "block px-3 py-1 text-sm leading-6 text-gray-900",
                    )}
                    to="/hub/settings"
                  >
                    Settings
                  </Link>
                )}
              </MenuItem>
              <MenuItem>
                {({ active }) => (
                  <button
                    className={clsx(
                      active ? "bg-gray-50" : "",
                      "block w-full text-left px-3 py-1 text-sm leading-6 text-gray-900",
                    )}
                    onClick={() => navigate("/logout")}
                  >
                    Sign out
                  </button>
                )}
              </MenuItem>
              {isImpersonating && (
                <>
                  <div className="border-t border-gray-100 my-1" />
                  <MenuItem>
                    {({ active }) => (
                      <button
                        className={clsx(
                          active ? "bg-gray-50" : "",
                          "block w-full text-left px-3 py-1 text-sm leading-6 text-red-600",
                        )}
                        onClick={handleExitImpersonation}
                      >
                        Exit Impersonation
                      </button>
                    )}
                  </MenuItem>
                </>
              )}
            </MenuItems>
          </Menu>
        </div>
      </div>

      <MobileSideNav onOpenChange={setMobileNavOpen} open={mobileNavOpen} />
    </>
  );
};

export default TopNavBar;
