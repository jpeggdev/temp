import React from "react";
import { Route } from "react-router-dom";
import MainLayout from "../../components/MainLayout/MainLayout";
import { AuthenticationGuard } from "@/components/AuthenticationGuard/AuthenticationGuard";
import PermissionGuard from "@/components/PermissionGuard/PermissionGuard";
import EmailTemplatesList from "@/modules/emailManagement/features/EmailTemplateManagement/components/EmailTemplateList/EmailTemplatesList";
import CreateEmailTemplate from "@/modules/emailManagement/features/EmailTemplateManagement/components/CreateEmailTemplate/CreateEmailTemplate";
import EditEmailTemplate from "@/modules/emailManagement/features/EmailTemplateManagement/components/EditEmailTemplate/EditEmailTemplate";
import EmailCampaignList from "@/modules/emailManagement/features/EmailCampaignManagement/components/EmailCampaignList/EmailCampaignList";
import CreateEmailCampaign from "@/modules/emailManagement/features/EmailCampaignManagement/components/CreateEmailCampaign/CreateEmailCampaign";
import EmailTemplateCategoryList from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/components/EmailTemplateCategoryList/EmailTemplateCategoryList";
import EmailEventLogList from "@/modules/emailManagement/features/EmailEventLogsManagement/component/EmailEventLogList/EmailEventLogList";
import UpdateEmailCampaign from "@/modules/emailManagement/features/EmailCampaignManagement/components/UpdateEmailCampaign/UpdateEmailCampaign";

/**
 * EmailManagementRoutes
 *
 * Wraps the /email-management section in the Auth0-based authentication guard, then
 * protects individual routes with PermissionGuard based on the required
 * permissions from your navigation config.
 */
const EmailManagementRoutes = (
  <Route
    element={
      <AuthenticationGuard
        component={() => <MainLayout section="email-management" />}
      />
    }
    path="/email-management"
  >
    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EmailTemplatesList />
        </PermissionGuard>
      }
      path="email-templates"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CreateEmailTemplate />
        </PermissionGuard>
      }
      path="email-template/new"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EditEmailTemplate />
        </PermissionGuard>
      }
      path="email-templates/:emailTemplateId/edit"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EmailTemplateCategoryList />
        </PermissionGuard>
      }
      path="email-template-categories"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EmailCampaignList />
        </PermissionGuard>
      }
      path="email-campaigns"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CreateEmailCampaign />
        </PermissionGuard>
      }
      path="email-campaign/new"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EmailEventLogList />
        </PermissionGuard>
      }
      path="email/activity"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <UpdateEmailCampaign />
        </PermissionGuard>
      }
      path="email-campaign/:emailCampaignId/edit"
    />
  </Route>
);

export default EmailManagementRoutes;
