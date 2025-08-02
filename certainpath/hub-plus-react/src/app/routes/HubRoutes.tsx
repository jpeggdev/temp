import React from "react";
import { Route } from "react-router-dom";
import MainLayout from "../../components/MainLayout/MainLayout";
import { AuthenticationGuard } from "@/components/AuthenticationGuard/AuthenticationGuard";
import PermissionGuard from "../../components/PermissionGuard/PermissionGuard";
import HubDashboard from "../../modules/hub/features/HubDashboard/HubDashboard";
import DashboardPage from "../../modules/hub/features/DashboardPage/components/HubDashboardPage/HubDashboardPage";
import MonthlyBalanceSheet from "../../modules/hub/features/DocumentLibrary/components/MonthlyBalanceSheet/MonthlyBalanceSheet";
import ProfitAndLoss from "../../modules/hub/features/DocumentLibrary/components/ProfitAndLoss/ProfitAndLoss";
import TransactionList from "../../modules/hub/features/DocumentLibrary/components/TransactionList/TransactionList";
import DataConnector from "../../modules/hub/features/HotglueDataConnector/components/DataConnector/DataConnector";
import UserList from "../../modules/hub/features/UserManagement/components/UserList/UserList";
import EditUser from "../../modules/hub/features/UserManagement/components/EditUser/EditUser";
import CreateUser from "../../modules/hub/features/UserManagement/components/CreateUser/CreateUser";
import EditBusinessRolesAndPermissionsList from "../../modules/hub/features/UserManagement/components/EditBusinessRolesAndPermissionsList/EditBusinessRolesAndPermissionsList";
import Settings from "../../modules/hub/features/UserAppSettings/components/Settings/Settings";
import CoachingDashboard from "@/modules/hub/features/CoachingDashboard/components/CoachingDasboard/CoachingDashboard";
import {
  ResourcesLibrary as ResourceLibrary
} from "@/modules/hub/features/ResourceLibrary/components/ResourceLibrary/ResourceLibrary";
import ResourceDetails from "@/modules/hub/features/ResourceLibrary/components/ResourceDetails/ResourceDetails";
import ResourceCategoryList from "@/modules/hub/features/ResourceCategoryManagement/components/ResourceCategoryList/ResourceCategoryList";
import ResourceTagList from "@/modules/hub/features/ResourceTagManagement/components/ResourceTagList/ResourceTagList";

/**
 * HubRoutes
 *
 * Wraps the /hub section in the Auth0-based authentication guard, then
 * protects individual routes with PermissionGuard based on the required
 * permissions from your navigation config.
 */
const HubRoutes = (
  <Route
    element={
      <AuthenticationGuard component={() => <MainLayout section="hub" />} />
    }
    path="/hub"
  >
    <Route element={<HubDashboard />} index />

    <Route element={<DashboardPage />} path="dashboards/field-labor" />

    <Route element={<CoachingDashboard />} path="dashboards/coaching" />

    <Route
      element={
        <PermissionGuard
          requiredPermissions={["CAN_ACCESS_MONTHLY_BALANCE_SHEET"]}
        >
          <MonthlyBalanceSheet />
        </PermissionGuard>
      }
      path="document-library/monthly-balance-sheet"
    />
    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_ACCESS_PROFIT_AND_LOSS"]}>
          <ProfitAndLoss />
        </PermissionGuard>
      }
      path="document-library/profit-and-loss"
    />
    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_ACCESS_TRANSACTION_LIST"]}>
          <TransactionList />
        </PermissionGuard>
      }
      path="document-library/transaction-list"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_ACCESS_DATA_CONNECTOR"]}>
          <DataConnector />
        </PermissionGuard>
      }
      path="data-connector"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_VIEW_USERS"]}>
          <UserList />
        </PermissionGuard>
      }
      path="users"
    />
    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_USERS"]}>
          <CreateUser />
        </PermissionGuard>
      }
      path="users/create"
    />
    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_USERS"]}>
          <EditUser />
        </PermissionGuard>
      }
      path="users/:uuid/edit"
    />
    <Route
      element={
        <PermissionGuard
          requiredPermissions={["CAN_MANAGE_ROLES_AND_PERMISSIONS"]}
        >
          <EditBusinessRolesAndPermissionsList />
        </PermissionGuard>
      }
      path="users/business-roles-permissions"
    />

    <Route element={<Settings />} path="settings" />

    <Route element={<ResourceLibrary />} path="resources" />
    <Route element={<ResourceDetails />} path="resources/:slug" />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <ResourceCategoryList />
        </PermissionGuard>
      }
      path="admin/resource-categories"
    />
    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <ResourceTagList />
        </PermissionGuard>
      }
      path="admin/resource-tags"
    />
  </Route>
);

export default HubRoutes;
