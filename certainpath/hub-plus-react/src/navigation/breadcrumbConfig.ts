import { matchPath } from "react-router-dom";

type ParamMap = Record<string, string | undefined>;

/**
 * A single route's breadcrumb definition.
 * - path: the route path (supports :params)
 * - label: either a string or a function to create a label from route params
 * - parent: the path string that points to a parent entry in this array or null if none
 */
export interface BreadcrumbRoute {
  path: string;
  label: string | ((params: ParamMap) => string);
  parent: string | null;
}

export const breadcrumbConfig: BreadcrumbRoute[] = [
  // -------------------------
  //  Existing /hub routes
  // -------------------------
  {
    path: "/hub",
    label: "Hub",
    parent: "/",
  },
  {
    path: "/hub/dashboards/field-labor",
    label: "Field Labor Dashboard",
    parent: "/hub",
  },
  {
    path: "/hub/dashboards/coaching",
    label: "Coaching Dashboard",
    parent: "/hub",
  },
  {
    path: "/hub/users",
    label: "Users",
    parent: "/hub",
  },
  {
    path: "/hub/admin/resource-categories",
    label: "Resource Categories",
    parent: "/hub",
  },
  {
    path: "/hub/admin/resource-tags",
    label: "Resource Tags",
    parent: "/hub",
  },
  {
    path: "/hub/users/create",
    label: "Create User",
    parent: "/hub/users",
  },
  {
    path: "/hub/users/:uuid/edit",
    label: (params) => `Editing user ${params.uuid ?? "Unknown"}`,
    parent: "/hub/users",
  },
  {
    path: "/hub/users/business-roles-permissions",
    label: "Business Roles & Permissions",
    parent: "/hub",
  },
  {
    path: "/hub/data-connector",
    label: "Data Connector",
    parent: "/hub",
  },
  {
    path: "/hub/settings",
    label: "Settings",
    parent: "/hub",
  },
  {
    path: "/hub/resources",
    label: "Resource Library",
    parent: "/hub",
  },
  {
    path: "/hub/document-library/monthly-balance-sheet",
    label: "Monthly Balance Sheet",
    parent: "/hub",
  },
  {
    path: "/hub/document-library/profit-and-loss",
    label: "Profit And Loss",
    parent: "/hub",
  },
  {
    path: "/hub/document-library/transaction-list",
    label: "Transaction List",
    parent: "/hub",
  },
  {
    path: "/hub/resources/:uuid",
    label: (params) => `Resource ${params.uuid ?? "Unknown"}`,
    parent: "/hub/resources",
  },

  // -------------------------
  //  New /stochastic routes
  // -------------------------
  {
    path: "/stochastic",
    label: "Stochastic Dashboard",
    parent: "/",
  },
  {
    path: "/stochastic/customers",
    label: "Customers",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/prospects",
    label: "Prospects",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/locations",
    label: "Locations",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/do-not-mail",
    label: "Do Not Mail",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/campaigns",
    label: "Campaigns",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/mailing",
    label: "Stochastic Mailing",
    parent: "/stochastic",
  },
  // Dynamic campaign + batch
  {
    path: "/stochastic/campaigns/:campaignId/batches",
    label: (params) => `Campaign ${params.campaignId ?? "Unknown"} Batches`,
    parent: "/stochastic/campaigns",
  },
  {
    path: "/stochastic/campaigns/:campaignId/batches/:batchId/prospects",
    label: (params) => `Batch ${params.batchId ?? "Unknown"} Prospects`,
    parent: "/stochastic/campaigns/:campaignId/batches",
  },
  {
    path: "/stochastic/campaigns/:campaignId/files",
    label: (params) => `Campaign ${params.campaignId ?? "Unknown"} Files`,
    parent: "/stochastic/campaigns",
  },
  {
    path: "/stochastic/field-service-import",
    label: "Field Service Import",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/prospect-source-import",
    label: "Prospect Source Import",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/do-not-mail-import",
    label: "Do not Mail Import",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/import-status",
    label: "Import Status",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/campaigns/new",
    label: "Create Campaign",
    parent: "/stochastic/campaigns",
  },
  {
    path: "/stochastic/campaigns/:campaignId/view",
    label: (params) => `Campaign ${params.campaignId ?? "Unknown"} Details`,
    parent: "/stochastic/campaigns",
  },
  {
    path: "/stochastic/products/campaign",
    label: "Campaign Products",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/campaigns/billing",
    label: "Campaign Billing",
    parent: "/stochastic",
  },
  {
    path: "/stochastic/campaigns/postage",
    label: "Postage Expense Import",
    parent: "/stochastic",
  },

  // -------------------------
  //  New /coaching routes
  // -------------------------
  {
    path: "/coaching",
    label: "Coaching Dashboard",
    parent: "/",
  },

  // -------------------------
  //  New /email-management routes
  // -------------------------
  {
    path: "/email-management",
    label: "Email Management",
    parent: "/",
  },
  {
    path: "/email-management/email-templates",
    label: "Email Templates",
    parent: "",
  },
  {
    path: "/email-management/email-template/new",
    label: "Create Email Template",
    parent: "/email-management/email-templates",
  },
  {
    path: "/email-management/email-templates/:emailTemplateId/edit",
    label: (params) =>
      `Editing Email Template: ${params.emailTemplateId ?? "Unknown"}`,
    parent: "/email-management/email-templates",
  },
  {
    path: "/email-management/email-campaigns",
    label: "Email Campaigns",
    parent: "/",
  },
  {
    path: "/email-management/email-campaign/new",
    label: "Create Email Campaign",
    parent: "/email-management/email-campaigns",
  },
  {
    path: "/email-management/email/activity",
    label: "Email Activity",
    parent: "/",
  },

  // -------------------------
  //  New /event-registration routes
  // -------------------------

  {
    path: "/event-registration/events",
    label: "Event Directory",
    parent: "/",
  },
  {
    path: "/event-registration/admin/events",
    label: "Event Management",
    parent: "/",
  },
  {
    path: "/event-registration/admin/events/new",
    label: "Create Event",
    parent: "/event-registration/admin/events",
  },
  {
    path: "/event-registration/admin/event-categories",
    label: "Event Categories",
    parent: "/",
  },
  {
    path: "/event-registration/admin/vouchers",
    label: "Vouchers",
    parent: "/",
  },
  {
    path: "/event-registration/admin/venues",
    label: "Venues",
    parent: "/",
  },
  {
    path: "/event-registration/admin/venue/:venueId/edit",
    label: (params) => `Editing Venue: ${params.venueId ?? "Unknown"}`,
    parent: "/event-registration/admin/venues",
  },
  {
    path: "/event-registration/admin/discounts",
    label: "Discounts",
    parent: "/",
  },
  {
    path: "/event-registration/admin/discount/:discountId/edit",
    label: (params) => `Editing Discount: ${params.discountId ?? "Unknown"}`,
    parent: "/event-registration/admin/discounts",
  },

  // -------------------------
  //  New /admin routes
  // -------------------------
  {
    path: "/admin",
    label: "Admin Dashboard",
    parent: "/",
  },
  {
    path: "/admin/companies",
    label: "Company Management",
    parent: "/",
  },
  {
    path: "/admin/companies/create",
    label: "Create Company",
    parent: "/admin/companies",
  },
  {
    path: "/admin/companies/:uuid/edit",
    label: (params) => `Editing Company: ${params.uuid ?? "Unknown"}`,
    parent: "/admin/companies",
  },
  {
    path: "/admin/resources",
    label: "Resource Management",
    parent: "/",
  },
  {
    path: "/admin/employee-roles",
    label: "Employee Roles",
    parent: "/",
  },
  {
    path: "/admin/resources/new",
    label: "Create Resource",
    parent: "/admin/resources",
  },
  {
    path: "/admin/resources/:uuid/edit",
    label: (params) => `Editing Resource: ${params.uuid ?? "Unknown"}`,
    parent: "/admin/resources",
  },
];

/**
 * Finds the first BreadcrumbRoute that exactly matches currentPath.
 */
export function findRouteConfig(currentPath: string): BreadcrumbRoute | null {
  for (const route of breadcrumbConfig) {
    const match = matchPath({ path: route.path, end: true }, currentPath);
    if (match) {
      return route;
    }
  }
  return null;
}

/**
 * Returns the params from the first route that matches currentPath.
 * e.g., /stochastic/campaigns/123/batches -> { campaignId: "123" }
 */
export function getRouteParams(currentPath: string): ParamMap {
  for (const route of breadcrumbConfig) {
    const match = matchPath({ path: route.path, end: true }, currentPath);
    if (match && match.params) {
      return match.params;
    }
  }
  return {};
}

/**
 * Builds an array of { path, label } from the top-level parent
 * down to the route that matches currentPath.
 */
export function buildBreadcrumbChain(currentPath: string): Array<{
  path: string;
  label: string;
}> {
  const currentRoute = findRouteConfig(currentPath);
  if (!currentRoute) {
    return [];
  }

  const chain: Array<{ path: string; label: string }> = [];

  let temp: BreadcrumbRoute | null = currentRoute;
  let pathToCheck = currentPath;

  while (temp) {
    // get the params for this route
    const urlParams = getRouteParams(pathToCheck);

    let label: string;
    if (typeof temp.label === "function") {
      label = temp.label(urlParams);
    } else {
      label = temp.label;
    }

    chain.unshift({
      path: temp.path,
      label,
    });

    if (!temp.parent) {
      break;
    }

    const parentRoute = findRouteConfig(temp.parent);
    if (!parentRoute) {
      break;
    }

    temp = parentRoute;
    pathToCheck = parentRoute.path;
  }

  return chain;
}
