import Bugsnag from "@bugsnag/js";
import BugsnagPluginReact from "@bugsnag/plugin-react";
import BugsnagPerformance from "@bugsnag/browser-performance";
import React, { ReactNode, ComponentType } from "react";

const hasBugsnagApiKey: boolean = !!process.env.REACT_APP_BUGSNAG_API_KEY;
const isDevEnvironment: boolean =
  process.env.REACT_APP_ENVIRONMENT === "development";

let ErrorBoundary: ComponentType<{ children?: ReactNode }> = ({ children }) => (
  <>{children}</>
);

if (hasBugsnagApiKey && !isDevEnvironment) {
  Bugsnag.start({
    apiKey: process.env.REACT_APP_BUGSNAG_API_KEY!,
    plugins: [new BugsnagPluginReact()],
  });

  BugsnagPerformance.start({ apiKey: process.env.REACT_APP_BUGSNAG_API_KEY! });

  const BugsnagErrorBoundary =
    Bugsnag.getPlugin("react")!.createErrorBoundary(React);

  ErrorBoundary = BugsnagErrorBoundary as ComponentType<{
    children?: ReactNode;
  }>;
}

export { ErrorBoundary };
export default Bugsnag;
