import {
  BuildingOffice2Icon,
  ArchiveBoxIcon,
  UserGroupIcon,
  FolderIcon,
} from "@heroicons/react/24/outline";
import { NavigationItem } from "./types";

/**
 * Each NavigationItem can have:
 * - name (string)
 * - href (string)
 * - icon (React component)
 * - current (boolean)
 * - internalName (string)
 * - permissions (string[])
 * - isCertainPathOnly (boolean)
 * - children?: NavigationItem[]
 */
const navigation: NavigationItem[] = [
  {
    name: "Company Management",
    href: "/admin/companies",
    icon: BuildingOffice2Icon,
    current: false,
    internalName: "company_management",
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: ["CAN_MANAGE_COMPANIES_ALL"],
    isCertainPathOnly: true,
  },
  {
    name: "Resource Management",
    href: "/admin/resources",
    icon: ArchiveBoxIcon,
    current: false,
    internalName: "resource_management",
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
    isCertainPathOnly: true,
  },
  {
    name: "Employee Roles",
    href: "/admin/employee-roles",
    icon: UserGroupIcon,
    current: false,
    internalName: "employee_roles",
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
    isCertainPathOnly: true,
  },
  {
    name: "File Manager",
    href: "/admin/file-manager",
    icon: FolderIcon,
    current: false,
    internalName: "file_manager",
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
    isCertainPathOnly: true,
  },
];

const adminConfig = {
  sectionName: "admin",
  defaultRoute: "/admin/companies",
  navigation,
};

export default adminConfig;
