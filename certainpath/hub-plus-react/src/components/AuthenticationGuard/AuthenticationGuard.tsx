import React from "react";
import { withAuthenticationRequired } from "@auth0/auth0-react";
import LoadingIndicator from "../LoadingIndicator/LoadingIndicator";

/**
 * @see https://developer.auth0.com/resources/guides/spa/react/basic-authentication
 * @param component
 * @constructor
 */
export function AuthenticationGuard({
  component,
}: {
  component: React.ComponentType;
}) {
  const Component = withAuthenticationRequired(component, {
    onRedirecting: () => <LoadingIndicator />,
  });

  return <Component />;
}
