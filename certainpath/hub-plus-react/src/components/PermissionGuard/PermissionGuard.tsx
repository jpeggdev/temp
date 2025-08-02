import React from "react";
import { Navigate } from "react-router-dom";
import permissionsService from "../../services/permissionsService";

interface PermissionGuardProps {
  children: React.ReactNode;
  requiredRoles?: string[];
  requiredPermissions?: string[];
}

export default function PermissionGuard({
  children,
  requiredRoles = [],
  requiredPermissions = [],
}: PermissionGuardProps) {
  const { hasRole, hasPermission } = permissionsService();

  const rolesPass =
    requiredRoles.length === 0 || requiredRoles.some((role) => hasRole(role));

  const permsPass =
    requiredPermissions.length === 0 ||
    requiredPermissions.every((perm) => hasPermission(perm));

  if (!rolesPass || !permsPass) {
    return <Navigate replace to="/403" />;
  }

  return <>{children}</>;
}
