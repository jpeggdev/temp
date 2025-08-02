export interface NavigationItem {
  name: string;
  internalName: string;
  permissions: string[];
  roles?: string[];
  href: string;
  icon: React.ComponentType<React.SVGProps<SVGSVGElement>>;
  current: boolean;
  children?: NavigationItem[];
  isCertainPathOnly?: boolean;
}
