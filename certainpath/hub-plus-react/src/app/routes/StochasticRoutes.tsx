import React from "react";
import { Route } from "react-router-dom";
import MainLayout from "../../components/MainLayout/MainLayout";
import { AuthenticationGuard } from "@/components/AuthenticationGuard/AuthenticationGuard";
import PermissionGuard from "@/components/PermissionGuard/PermissionGuard";
import DashboardPage from "@/modules/stochastic/features/DashboardPage/components/DashboardPage/DashboardPage";
import CustomerList from "../../modules/stochastic/features/CustomerList/components/CustomerList/CustomerList";
import ProspectList from "../../modules/stochastic/features/ProspectList/components/ProspectList/ProspectList";
import DoNotMailList from "@/modules/stochastic/features/DoNotMailManagement/components/DoNotMailList/DoNotMailList";
import CampaignList from "../../modules/stochastic/features/CampaignManagement/components/CampaignList/CampaignList";
import StochasticMailingList from "@/modules/stochastic/features/StochasticMailing/components/StochasticMailingList/StochasticMailingList";
import BatchProspectList from "../../modules/stochastic/features/BatchProspectManagement/components/BatchProspectList/BatchProspectList";
import CampaignBatchList from "../../modules/stochastic/features/CampaignBatchManagement/components/CampaignBatchList/CampaignBatchList";
import CampaignBillingList from "../../modules/stochastic/features/CampaignBillingManagement/components/CampaignBillingList/CampaignBillingList";
import CampaignFilesList from "../../modules/stochastic/features/CampaignFileManagement/components/CampaignFilesList/CampaignFilesList";
import { FieldServiceImport } from "@/modules/stochastic/features/FieldServiceImport/components/FieldServiceImport/FieldServiceImport";
import { ProspectSourceImport } from "@/modules/stochastic/features/ProspectSourceImport/components/ProspectSourceImport/ProspectSourceImport";
import ImportStatus from "@/modules/stochastic/features/ImportStatus/components/ImportStatus/ImportStatus";
import CreateCampaignPage from "@/modules/stochastic/features/CampaignManagement/components/CreateCampaignPage/CreateCampaignPage";
import ViewCampaignDetailsPage from "@/modules/stochastic/features/CampaignManagement/components/ViewCampaignDetailsPage/ViewCampaignDetailsPage";
import CampaignProductList from "@/modules/stochastic/features/CampaignProductManagement/components/CampaignProductList/CampaignProductList";
import { PostageExpenseImport } from "@/modules/stochastic/features/PostageExpenseImport/components/PostageExpenseImport/PostageExpenseImport";
import LocationsList from "@/modules/stochastic/features/LocationsList/components/LocationList/LocationsList";
import { DoNotMailListImport } from "@/modules/stochastic/features/DoNotMailImport/components/DoNotMailListImport/DoNotMailListImport";

const StochasticRoutes = (
  <Route
    element={
      <AuthenticationGuard
        component={() => <MainLayout section="stochastic" />}
      />
    }
    path="/stochastic"
  >
    <Route element={<DashboardPage />} index />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_CUSTOMERS"]}>
          <CustomerList />
        </PermissionGuard>
      }
      path="customers"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_PROSPECTS"]}>
          <ProspectList />
        </PermissionGuard>
      }
      path="prospects"
    />

    <Route element={<LocationsList />} path="locations" />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <DoNotMailList />
        </PermissionGuard>
      }
      path="do-not-mail"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_CAMPAIGNS"]}>
          <CampaignList />
        </PermissionGuard>
      }
      path="campaigns"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_VIEW_STOCHASTIC_MAILING"]}>
          <StochasticMailingList />
        </PermissionGuard>
      }
      path="mailing"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <CampaignBillingList />
        </PermissionGuard>
      }
      path="campaigns/billing"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_CAMPAIGN_BATCHES"]}>
          <CampaignBatchList />
        </PermissionGuard>
      }
      path="campaigns/:campaignId/batches"
    />
    <Route
      element={
        <PermissionGuard
          requiredPermissions={["CAN_MANAGE_CAMPAIGN_BATCH_PROSPECTS"]}
        >
          <BatchProspectList />
        </PermissionGuard>
      }
      path="campaigns/:campaignId/batches/:batchId/prospects"
    />

    {/* Files, FieldServiceImport, ProspectSourceImport now require roles instead of permissions */}
    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <CampaignFilesList />
        </PermissionGuard>
      }
      path="campaigns/:campaignId/files"
    />
    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <FieldServiceImport />
        </PermissionGuard>
      }
      path="field-service-import"
    />
    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <ProspectSourceImport />
        </PermissionGuard>
      }
      path="prospect-source-import"
    />
    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <DoNotMailListImport />
        </PermissionGuard>
      }
      path="do-not-mail-import"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <ImportStatus />
        </PermissionGuard>
      }
      path="import-status"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_CREATE_CAMPAIGNS"]}>
          <CreateCampaignPage />
        </PermissionGuard>
      }
      path="campaigns/new"
    />

    <Route
      element={
        <PermissionGuard requiredPermissions={["CAN_MANAGE_CAMPAIGNS"]}>
          <ViewCampaignDetailsPage />
        </PermissionGuard>
      }
      path="campaigns/:campaignId/view"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CampaignProductList />
        </PermissionGuard>
      }
      path="products/campaign"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN", "ROLE_MARKETING"]}>
          <PostageExpenseImport />
        </PermissionGuard>
      }
      path="campaigns/postage"
    />
  </Route>
);

export default StochasticRoutes;
