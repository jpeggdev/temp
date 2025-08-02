"use client";

import React, { useState } from "react";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import clsx from "clsx";
import { UserCircleIcon, Cog6ToothIcon } from "@heroicons/react/24/outline";
import UserProfile from "../UserProfile/UserProfile";
import CompanyProfile from "../CompanyProfile/CompanyProfile";

const Settings: React.FC = () => {
  const [currentTab, setCurrentTab] = useState("User Profile");

  const tabs = [
    { name: "User Profile", key: "userProfile", icon: UserCircleIcon },
    { name: "Company Profile", key: "companyProfile", icon: Cog6ToothIcon },
    /*{ name: "Security", key: "security", icon: LockClosedIcon },*/
  ];

  return (
    <MainPageWrapper error={null} loading={false} title="Settings">
      <div className="w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="mb-10">
          <div className="border-b border-gray-200">
            <nav aria-label="Tabs" className="-mb-px flex space-x-8">
              {tabs.map((tab) => (
                <button
                  aria-current={currentTab === tab.name ? "page" : undefined}
                  className={clsx(
                    currentTab === tab.name
                      ? "border-indigo-500 text-indigo-600"
                      : "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700",
                    "flex items-center whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium",
                  )}
                  key={tab.key}
                  onClick={() => setCurrentTab(tab.name)}
                >
                  <tab.icon aria-hidden="true" className="h-5 w-5 mr-2" />
                  {tab.name}
                </button>
              ))}
            </nav>
          </div>
        </div>

        {currentTab === "User Profile" && <UserProfile />}
        {currentTab === "Company Profile" && <CompanyProfile />}
        {/*{currentTab === "Security" && <Security />}*/}
      </div>
    </MainPageWrapper>
  );
};

export default Settings;
