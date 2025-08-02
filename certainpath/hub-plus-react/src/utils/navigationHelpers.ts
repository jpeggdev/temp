import { NavigationItem } from "@/navigation/types";
import hubConfig from "@/navigation/hubNavigation";
import adminConfig from "@/navigation/adminNavigation";
import stochasticConfig from "@/navigation/stochasticNavigation";
import eventRegistrationConfig from "@/navigation/eventRegistrationNavigation";
import coachingConfig from "@/navigation/coachingNavigation";
import emailManagementConfig from "@/navigation/emailManagementNavigation";

const allSectionConfigs = [
  hubConfig,
  coachingConfig,
  eventRegistrationConfig,
  stochasticConfig,
  emailManagementConfig,
  adminConfig,
];

function flattenNavigationItems(items: NavigationItem[]): NavigationItem[] {
  const result: NavigationItem[] = [];
  items.forEach((item) => {
    result.push(item);
    if (item.children) {
      result.push(...flattenNavigationItems(item.children));
    }
  });
  return result;
}

export interface SectionResult {
  sectionName: string;
  defaultRoute: string;
}

export function getSectionFromPath(path: string): SectionResult | null {
  for (const { sectionName, defaultRoute, navigation } of allSectionConfigs) {
    const flattenedItems = flattenNavigationItems(navigation);
    const isMatch = flattenedItems.some(
      (item) => item.href !== "#" && path.startsWith(item.href),
    );
    if (isMatch) {
      return { sectionName, defaultRoute };
    }
  }
  return null;
}

export function filterNavigationByAccess(
  items: NavigationItem[],
  hasPermission: (perm: string) => boolean,
  hasRole: (role: string) => boolean,
  hasCertainPathCompany: boolean,
): NavigationItem[] {
  return items
    .filter((item) => {
      let rolesPass = true;
      if (item.roles && item.roles.length > 0) {
        rolesPass = item.roles.some((r) => hasRole(r));
      }

      let permsPass = true;
      if (item.permissions && item.permissions.length > 0) {
        permsPass = item.permissions.every((p) => hasPermission(p));
      }

      let cpPass = true;
      if (item.isCertainPathOnly) {
        cpPass = hasCertainPathCompany;
      }

      return rolesPass && permsPass && cpPass;
    })
    .map((item) => ({
      ...item,
      children: item.children
        ? filterNavigationByAccess(
            item.children,
            hasPermission,
            hasRole,
            hasCertainPathCompany,
          )
        : undefined,
    }));
}
