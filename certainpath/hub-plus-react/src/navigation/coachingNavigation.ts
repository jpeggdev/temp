import { ChartBarIcon } from "@heroicons/react/24/outline";
import { NavigationItem } from "./types";

const navigation: NavigationItem[] = [
  {
    name: "Dashboard",
    href: "/coaching",
    icon: ChartBarIcon,
    current: false,
    internalName: "coaching",
    permissions: [],
  },
];

const coachingConfig = {
  sectionName: "coaching",
  defaultRoute: "/coaching",
  navigation,
};

export default coachingConfig;
