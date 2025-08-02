import { useSelector } from "react-redux";
import { UserAppSettings } from "../api/fetchUserAppSettings/types";
import { RootState } from "../app/rootReducer";

const permissionsService = () => {
  const userAppSettings: UserAppSettings | null = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );

  const hasPermission = (permission: string): boolean => {
    if (!userAppSettings || !userAppSettings.permissions) {
      return false;
    }
    return userAppSettings.permissions.includes(permission);
  };

  const hasApplicationAccess = (applicationName: string): boolean => {
    if (!userAppSettings || !userAppSettings.applicationAccess) {
      return false;
    }
    return userAppSettings.applicationAccess.includes(applicationName);
  };

  const hasRole = (role: string): boolean => {
    if (!userAppSettings || !userAppSettings.roleName) {
      return false;
    }
    return userAppSettings.roleName === role;
  };

  const hasCertainPathCompany = () => {
    if (!userAppSettings) return false;
    return userAppSettings.isCertainPathCompany;
  };

  return {
    hasPermission,
    hasApplicationAccess,
    hasRole,
    hasCertainPathCompany,
  };
};

export default permissionsService;
