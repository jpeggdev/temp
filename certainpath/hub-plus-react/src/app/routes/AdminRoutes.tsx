import React from "react";
import { Route } from "react-router-dom";
import MainLayout from "../../components/MainLayout/MainLayout";
import { AuthenticationGuard } from "../../components/AuthenticationGuard/AuthenticationGuard";
import CompanyManagement from "@/modules/hub/features/CompanyManagement/components/CompanyList/CompanyList";
import PermissionGuard from "../../components/PermissionGuard/PermissionGuard";
import CreateCompany from "@/modules/hub/features/CompanyManagement/components/CreateCompany/CreateCompany";
import EditCompany from "@/modules/hub/features/CompanyManagement/components/EditCompany/EditCompany";
import CreateResource from "@/modules/hub/features/ResourceManagement/components/CreateResource/CreateResource";
import EditResource from "@/modules/hub/features/ResourceManagement/components/EditResource/EditResource";
import ResourceList from "@/modules/hub/features/ResourceManagement/components/ResourceList/ResourceList";
import EmployeeRoleList from "@/modules/hub/features/EmployeeRoleManagement/components/EmployeeRoleList/EmployeeRoleList";
import FileManagement from "@/modules/hub/features/FileManagement/components/FileManagement/FileManagement";

/**
 * AdminRoutes
 *
 * Wraps the /admin area in our Auth0-based authentication guard.
 * Then for single-route permission checks, we wrap each route element
 * with <PermissionGuard>.
 */
const AdminRoutes = (
  <Route
    element={
      <AuthenticationGuard component={() => <MainLayout section="admin" />} />
    }
    path="/admin"
  >
    {/* List / Index page */}
    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_COMPANIES_ALL"]}>
          <CompanyManagement />
        </PermissionGuard>
      }
      index
    />

    {/* Company Management listing */}
    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_COMPANIES_ALL"]}>
          <CompanyManagement />
        </PermissionGuard>
      }
      path="companies"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_CREATE_COMPANIES"]}>
          <CreateCompany />
        </PermissionGuard>
      }
      path="companies/create"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_EDIT_COMPANIES"]}>
          <EditCompany />
        </PermissionGuard>
      }
      path="companies/:uuid/edit"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CreateResource />
        </PermissionGuard>
      }
      path="resources/new"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EditResource />
        </PermissionGuard>
      }
      path="resources/:uuid/edit"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <ResourceList />
        </PermissionGuard>
      }
      path="resources"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EmployeeRoleList />
        </PermissionGuard>
      }
      path="employee-roles"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <FileManagement />
        </PermissionGuard>
      }
      path="file-manager"
    />
  </Route>
);

export default AdminRoutes;
