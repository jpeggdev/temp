import React from "react";
import permissionsService from "../../services/permissionsService";

interface ShowIfHasAccessProps {
  requiredRoles?: string[];
  requiredPermissions?: string[];
  children: React.ReactNode;
}

export default function ShowIfHasAccess({
  requiredRoles = [],
  requiredPermissions = [],
  children,
}: ShowIfHasAccessProps) {
  const { hasRole, hasPermission } = permissionsService();

  const rolesPass =
    requiredRoles.length === 0 || requiredRoles.some((role) => hasRole(role));

  const permsPass =
    requiredPermissions.length === 0 ||
    requiredPermissions.every((perm) => hasPermission(perm));

  if (rolesPass && permsPass) {
    return <>{children}</>;
  }

  return null;
}
