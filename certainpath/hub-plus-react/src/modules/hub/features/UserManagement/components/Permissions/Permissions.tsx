import React, { useEffect, useState } from "react";
import { Switch } from "@headlessui/react";
import CertainPathTextInput from "../../../../../../components/CertainPathTextInput/CertainPathTextInput";
import { useDispatch, useSelector } from "react-redux";
import { updateEmployeePermissionAction } from "../../slices/editUserDetailsSlice";
import { fetchUserAppSettingsAction } from "../../../UserAppSettings/slices/userAppSettingsSlice";
import { PermissionGroup } from "../../../../../../api/getEditUserDetails/types";
import { selectEmployeeUuid } from "../../../UserAppSettings/selectors/userAppSettingsSelectors";

interface PermissionsProps {
  availablePermissionGroups: PermissionGroup[];
  employeeRolePermissions: number[];
  employeeAdditionalPermissions: number[];
  uuid: string;
}

const Permissions: React.FC<PermissionsProps> = ({
  availablePermissionGroups,
  employeeRolePermissions,
  employeeAdditionalPermissions,
  uuid,
}) => {
  const [selectedPermissions, setSelectedPermissions] = useState<
    Record<number, boolean>
  >({});
  const [searchTerm, setSearchTerm] = useState<string>("");
  const dispatch = useDispatch();

  const employeeUuid = useSelector(selectEmployeeUuid);

  useEffect(() => {
    const initialSelectedPermissions: Record<number, boolean> = {};
    availablePermissionGroups.forEach((group) => {
      group.permissions.forEach((perm) => {
        initialSelectedPermissions[perm.permissionId] =
          employeeRolePermissions.includes(perm.permissionId) ||
          employeeAdditionalPermissions.includes(perm.permissionId);
      });
    });
    setSelectedPermissions(initialSelectedPermissions);
  }, [
    availablePermissionGroups,
    employeeRolePermissions,
    employeeAdditionalPermissions,
  ]);

  const handleTogglePermission = (permissionId: number) => {
    const isActive = !selectedPermissions[permissionId];

    dispatch(
      updateEmployeePermissionAction(
        uuid,
        { permissionId, active: isActive },
        () => {
          if (uuid === employeeUuid) {
            dispatch(fetchUserAppSettingsAction(false));
          }
        },
      ),
    );

    setSelectedPermissions((prev) => ({
      ...prev,
      [permissionId]: isActive,
    }));
  };

  const filteredGroups = availablePermissionGroups.map((group) => ({
    ...group,
    permissions: group.permissions.filter((perm) =>
      [perm.name, perm.label, perm.description].some((field) =>
        field.toLowerCase().includes(searchTerm.toLowerCase()),
      ),
    ),
  }));

  return (
    <div className="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow">
      <div className="mb-4">
        <CertainPathTextInput
          name="search"
          onChange={(e) => setSearchTerm(e.target.value)}
          placeholder="Search for a permission..."
          value={searchTerm}
        />
      </div>

      {filteredGroups.map((group) => (
        <div className="mb-6" key={group.groupName}>
          <h3 className="text-lg font-semibold text-gray-900 mb-4">
            {group.groupName}
          </h3>
          {group.permissions.length > 0 ? (
            group.permissions.map((permission) => {
              const isDisabled = employeeRolePermissions.includes(
                permission.permissionId,
              );

              return (
                <div
                  className="flex items-start justify-between p-4 border rounded-lg shadow-sm bg-gray-50"
                  key={permission.permissionId}
                >
                  <div className="flex-1 text-sm">
                    <label
                      className={`flex items-center font-medium ${
                        isDisabled ? "text-gray-400" : "text-gray-900"
                      }`}
                      htmlFor={`permission-${permission.permissionId}`}
                    >
                      {permission.label}
                      {permission.isCertainPathOnly && (
                        <span className="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                          Certain Path Only
                        </span>
                      )}
                    </label>
                    <p
                      className={isDisabled ? "text-gray-400" : "text-gray-500"}
                    >
                      {permission.description}
                    </p>
                  </div>
                  <Switch
                    checked={
                      selectedPermissions[permission.permissionId] || false
                    }
                    className={`${
                      selectedPermissions[permission.permissionId]
                        ? "bg-indigo-600"
                        : "bg-gray-200"
                    } relative inline-flex h-6 w-11 items-center rounded-full ${
                      isDisabled ? "opacity-50 cursor-not-allowed" : ""
                    }`}
                    disabled={isDisabled}
                    onChange={() =>
                      handleTogglePermission(permission.permissionId)
                    }
                  >
                    <span
                      className={`${
                        selectedPermissions[permission.permissionId]
                          ? "translate-x-6"
                          : "translate-x-1"
                      } inline-block h-4 w-4 transform rounded-full bg-white transition`}
                    />
                  </Switch>
                </div>
              );
            })
          ) : (
            <p className="text-center text-gray-500">
              No permissions found in this group.
            </p>
          )}
        </div>
      ))}
    </div>
  );
};

export default Permissions;
