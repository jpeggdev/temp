import { HomeIcon, FolderIcon } from "@heroicons/react/24/outline";
import { NavigationItem } from "./types";
import {
  BookOpenIcon,
  GraduationCap,
  UserRoundPlus,
  TicketIcon,
  Building2,
  Percent,
} from "lucide-react";

const navigation: NavigationItem[] = [
  {
    name: "Event Directory",
    href: "/event-registration/events",
    icon: BookOpenIcon,
    current: false,
    internalName: "event-directory",
    permissions: [],
  },
  {
    name: "Admin",
    href: "/event-registration/admin",
    icon: HomeIcon,
    current: false,
    internalName: "event-registration-admin",
    roles: ["ROLE_SUPER_ADMIN"],
    permissions: [],
    isCertainPathOnly: true,
    children: [
      {
        name: "Management",
        href: "/event-registration/admin/events",
        icon: UserRoundPlus,
        current: false,
        isCertainPathOnly: true,
        roles: ["ROLE_SUPER_ADMIN"],
        internalName: "event-management",
        permissions: [],
      },
      {
        name: "Categories",
        href: "/event-registration/admin/event-categories",
        icon: FolderIcon,
        current: false,
        isCertainPathOnly: true,
        roles: ["ROLE_SUPER_ADMIN"],
        internalName: "event_categories",
        permissions: [],
      },
      {
        name: "Instructors",
        href: "/event-registration/admin/event-instructors",
        icon: GraduationCap,
        current: false,
        isCertainPathOnly: true,
        roles: ["ROLE_SUPER_ADMIN"],
        internalName: "event_instructors",
        permissions: [],
      },
      {
        name: "Venues",
        href: "/event-registration/admin/venues",
        icon: Building2,
        current: false,
        isCertainPathOnly: true,
        roles: ["ROLE_SUPER_ADMIN"],
        internalName: "event_venues",
        permissions: [],
      },
      {
        name: "Vouchers",
        href: "/event-registration/admin/vouchers",
        icon: TicketIcon,
        current: false,
        isCertainPathOnly: true,
        roles: ["ROLE_SUPER_ADMIN"],
        internalName: "event_vouchers",
        permissions: [],
      },
      {
        name: "Discounts",
        href: "/event-registration/admin/discounts",
        icon: Percent,
        current: false,
        isCertainPathOnly: true,
        roles: ["ROLE_SUPER_ADMIN"],
        internalName: "event_discounts",
        permissions: [],
      },
    ],
  },
];

const eventRegistrationConfig = {
  sectionName: "event-registration",
  defaultRoute: "/event-registration/events",
  navigation,
};

export default eventRegistrationConfig;
