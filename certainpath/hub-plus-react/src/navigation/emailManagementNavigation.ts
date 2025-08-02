import {
  EnvelopeIcon,
  FolderIcon,
  PaperAirplaneIcon,
  ChartBarIcon,
} from "@heroicons/react/24/outline";
import { NavigationItem } from "./types";

const navigation: NavigationItem[] = [
  {
    name: "Email Templates",
    href: "/email-management/email-templates",
    icon: EnvelopeIcon,
    current: false,
    internalName: "email_templates",
    isCertainPathOnly: true,
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
  },
  {
    name: "Email Campaigns",
    href: "/email-management/email-campaigns",
    icon: PaperAirplaneIcon,
    current: false,
    internalName: "email_campaigns",
    isCertainPathOnly: true,
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
  },
  {
    name: "Email Template Categories",
    href: "/email-management/email-template-categories",
    icon: FolderIcon,
    current: false,
    internalName: "email_template_categories",
    isCertainPathOnly: true,
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
  },
  {
    name: "Email Event Logs",
    href: "/email-management/email/activity",
    icon: ChartBarIcon,
    current: false,
    internalName: "email_event_logs",
    isCertainPathOnly: true,
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
  },
];

const emailManagementConfig = {
  sectionName: "email_management_navigation",
  defaultRoute: "/email-management/email-templates",
  navigation,
};

export default emailManagementConfig;
