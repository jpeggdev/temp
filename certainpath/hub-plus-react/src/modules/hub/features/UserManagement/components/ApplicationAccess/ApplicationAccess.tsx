import React, { useState, useEffect } from "react";
import { Switch } from "@headlessui/react";
import { useDispatch, useSelector } from "react-redux";
import {
  Application,
  ApplicationAccessRecord,
} from "../../../../../../api/getEditUserDetails/types";
import { updateEmployeeApplicationAccessAction } from "../../slices/editUserDetailsSlice";
import { fetchUserAppSettingsAction } from "../../../UserAppSettings/slices/userAppSettingsSlice";
import { selectEmployeeUuid } from "../../../UserAppSettings/selectors/userAppSettingsSelectors";

interface ApplicationAccessProps {
  availableApplications: Application[];
  employeeApplicationAccess: ApplicationAccessRecord[];
  uuid: string;
}

const ApplicationAccess: React.FC<ApplicationAccessProps> = ({
  availableApplications,
  employeeApplicationAccess,
  uuid,
}) => {
  const [enabledApps, setEnabledApps] = useState<Record<number, boolean>>({});
  const dispatch = useDispatch();

  const employeeUuid = useSelector(selectEmployeeUuid);

  useEffect(() => {
    const initialEnabledApps: Record<number, boolean> = {};
    availableApplications.forEach((app) => {
      initialEnabledApps[app.id] = employeeApplicationAccess.some(
        (access) => access.applicationId === app.id,
      );
    });
    setEnabledApps(initialEnabledApps);
  }, [availableApplications, employeeApplicationAccess]);

  const handleToggle = (appId: number) => {
    const isActive = !enabledApps[appId];

    dispatch(
      updateEmployeeApplicationAccessAction(
        uuid,
        { applicationId: appId, active: isActive },
        () => {
          if (uuid === employeeUuid) {
            dispatch(fetchUserAppSettingsAction(false));
          }
        },
      ),
    );
    setEnabledApps((prevState) => ({
      ...prevState,
      [appId]: isActive,
    }));
  };

  return (
    <div className="max-w-3xl p-4">
      <fieldset className="border-b border-t border-gray-200">
        <legend className="sr-only">Application Access</legend>
        <div className="divide-y divide-gray-200">
          {availableApplications.map((app) => (
            <div
              className="flex items-center justify-between py-4"
              key={app.id}
            >
              <div className="flex flex-grow flex-col">
                <label
                  className="text-sm font-medium text-gray-900"
                  htmlFor={`app-${app.id}`}
                >
                  {app.name}
                </label>
              </div>
              <Switch
                checked={enabledApps[app.id] || false}
                className={`${
                  enabledApps[app.id] ? "bg-indigo-600" : "bg-gray-200"
                } relative inline-flex h-6 w-11 items-center rounded-full`}
                onChange={() => handleToggle(app.id)} // Call the handleToggle function
              >
                <span
                  className={`${
                    enabledApps[app.id] ? "translate-x-6" : "translate-x-1"
                  } inline-block h-4 w-4 transform rounded-full bg-white transition`}
                />
              </Switch>
            </div>
          ))}
        </div>
      </fieldset>
    </div>
  );
};

export default ApplicationAccess;
