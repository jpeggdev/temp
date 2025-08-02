import {
  BuildingLibraryIcon,
  AcademicCapIcon,
  PuzzlePieceIcon,
  ReceiptPercentIcon,
  TrophyIcon,
  Cog8ToothIcon,
  ChartBarIcon,
  EnvelopeIcon,
} from "@heroicons/react/24/outline";
import { NavigationItem } from "./types";

const skinnyNavigation: NavigationItem[] = [
  {
    name: "Hub",
    internalName: "hub",
    href: "/hub",
    icon: BuildingLibraryIcon,
    current: false,
    permissions: [],
  },
  {
    name: "Event Registration",
    internalName: "event_registration",
    href: "/event-registration/event-directory",
    icon: AcademicCapIcon,
    current: false,
    permissions: [],
  },
  {
    name: "Stochastic",
    internalName: "stochastic",
    href: "/stochastic",
    icon: PuzzlePieceIcon,
    current: false,
    permissions: [],
  },
  {
    name: "Email Management",
    internalName: "email_management",
    href: "/email-management/email-templates",
    icon: EnvelopeIcon,
    current: false,
    permissions: [],
    roles: ["ROLE_SUPER_ADMIN"],
  },
  {
    name: "Coaching",
    internalName: "coaching",
    href: "/coaching",
    icon: ChartBarIcon,
    current: false,
    permissions: [],
  },
  {
    name: "Partner Network",
    internalName: "partner_network",
    href: "#",
    icon: ReceiptPercentIcon,
    current: false,
    permissions: [],
  },
  {
    name: "Scoreboard",
    internalName: "scoreboard",
    href: "#",
    icon: TrophyIcon,
    current: false,
    permissions: [],
  },
  {
    name: "Admin",
    internalName: "admin",
    href: "/admin",
    icon: Cog8ToothIcon,
    current: false,
    permissions: ["CAN_MANAGE_COMPANIES_ALL"],
    roles: ["ROLE_SUPER_ADMIN", "ROLE_MARKETING"],
    isCertainPathOnly: true,
  },
];

export default skinnyNavigation;
