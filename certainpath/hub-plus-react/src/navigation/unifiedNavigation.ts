import { NavigationItem } from "@/navigation/types";
import hubConfig from "@/navigation/hubNavigation";
import adminConfig from "@/navigation/adminNavigation";
import stochasticConfig from "@/navigation/stochasticNavigation";
import eventRegistrationConfig from "@/navigation/eventRegistrationNavigation";
import EmailManagementConfig from "@/navigation/emailManagementNavigation";
import { HomeIcon, Squares2X2Icon } from "@heroicons/react/24/outline";

export function getUnifiedNavigation(): NavigationItem[] {
  return [
    {
      name: "Hub",
      internalName: "hub",
      permissions: [],
      href: "#",
      icon: HomeIcon,
      current: false,
      children: hubConfig.navigation,
    },
    // {
    //   name: "Coaching",
    //   internalName: "coaching",
    //   permissions: [],
    //   href: "#",
    //   icon: Squares2X2Icon,
    //   current: false,
    //   children: coachingConfig.navigation,
    // },
    {
      name: "Event Registration",
      internalName: "event_registration",
      permissions: [],
      href: "#",
      icon: Squares2X2Icon,
      current: false,
      children: eventRegistrationConfig.navigation,
    },
    {
      name: "Stochastic",
      internalName: "stochastic",
      permissions: [],
      href: "#",
      icon: Squares2X2Icon,
      current: false,
      children: stochasticConfig.navigation,
    },
    {
      name: "Email Management",
      internalName: "email_management",
      permissions: [],
      href: "$",
      icon: Squares2X2Icon,
      current: false,
      roles: ["ROLE_SUPER_ADMIN"],
      children: EmailManagementConfig.navigation,
      isCertainPathOnly: true,
    },
    {
      name: "Admin",
      internalName: "admin",
      permissions: [],
      href: "#",
      icon: Squares2X2Icon,
      current: false,
      roles: ["ROLE_SUPER_ADMIN"],
      children: adminConfig.navigation,
      isCertainPathOnly: true,
    },
  ];
}
