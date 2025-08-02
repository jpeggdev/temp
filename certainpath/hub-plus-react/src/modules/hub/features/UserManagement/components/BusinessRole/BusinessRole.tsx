import React, { useState, useEffect } from "react";
import { Radio, RadioGroup } from "@headlessui/react";
import { useDispatch, useSelector } from "react-redux";
import clsx from "clsx";
import { Role } from "../../../../../../api/getEditUserDetails/types";
import {
  refreshUserPermissionsAction,
  updateEmployeeBusinessRoleAction,
} from "../../slices/editUserDetailsSlice";
import { fetchUserAppSettingsAction } from "../../../UserAppSettings/slices/userAppSettingsSlice";
import { selectEmployeeUuid } from "../../../UserAppSettings/selectors/userAppSettingsSelectors";

interface BusinessRoleProps {
  availableRoles: Role[];
  employeeBusinessRoleId: number | null;
  uuid: string;
}

const BusinessRole: React.FC<BusinessRoleProps> = ({
  availableRoles,
  employeeBusinessRoleId,
  uuid,
}) => {
  const [selectedRole, setSelectedRole] = useState<Role | null>(null);
  const dispatch = useDispatch();
  const employeeUuid = useSelector(selectEmployeeUuid);

  useEffect(() => {
    if (availableRoles && employeeBusinessRoleId !== null) {
      const role = availableRoles.find((r) => r.id === employeeBusinessRoleId);
      setSelectedRole(role || null);
    }
  }, [availableRoles, employeeBusinessRoleId]);

  const handleRoleChange = (role: Role) => {
    setSelectedRole(role);
    dispatch(
      updateEmployeeBusinessRoleAction(
        uuid,
        { businessRoleId: role.id },
        () => {
          dispatch(refreshUserPermissionsAction(uuid));
          if (uuid === employeeUuid) {
            dispatch(fetchUserAppSettingsAction(false));
          }
        },
      ),
    );
  };

  return (
    <fieldset aria-label="Select a Role">
      <RadioGroup
        className="-space-y-px rounded-md bg-white"
        onChange={handleRoleChange}
        value={selectedRole}
      >
        {availableRoles.map((role, roleIdx) => (
          <Radio
            className={clsx(
              roleIdx === 0 ? "rounded-tl-md rounded-tr-md" : "",
              roleIdx === availableRoles.length - 1
                ? "rounded-bl-md rounded-br-md"
                : "",
              "group relative flex cursor-pointer border border-gray-200 p-4 focus:outline-none",
              "data-[checked]:z-10 data-[checked]:border-indigo-200 data-[checked]:bg-indigo-50",
            )}
            key={role.id}
            value={role}
          >
            <span
              aria-hidden="true"
              className={clsx(
                "mt-0.5 flex h-4 w-4 shrink-0 cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white",
                "group-data-[checked]:border-transparent group-data-[checked]:bg-indigo-600 group-focus:ring-2 group-focus:ring-indigo-600 group-focus:ring-offset-2",
              )}
            >
              <span className="h-1.5 w-1.5 rounded-full bg-white" />
            </span>

            <span className="ml-3 flex flex-col">
              <span
                className={clsx(
                  "block text-sm font-medium text-gray-900",
                  "group-data-[checked]:text-indigo-900",
                )}
              >
                {role.label}
                {role.isCertainPathOnly && (
                  <span className="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                    Certain Path Only
                  </span>
                )}
              </span>

              {role.description && (
                <span
                  className={clsx(
                    "block text-sm text-gray-500",
                    "group-data-[checked]:text-indigo-700",
                  )}
                >
                  {role.description}
                </span>
              )}
            </span>
          </Radio>
        ))}
      </RadioGroup>
    </fieldset>
  );
};

export default BusinessRole;
