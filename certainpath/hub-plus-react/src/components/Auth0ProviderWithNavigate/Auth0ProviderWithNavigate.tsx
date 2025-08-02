import React from "react";
import { Auth0Provider } from "@auth0/auth0-react";
import { useNavigate } from "react-router-dom";

interface AppState {
  returnTo?: string;
}

export function Auth0ProviderWithNavigate({
  children,
}: {
  children: React.ReactNode;
}) {
  const navigate = useNavigate();

  const domain = process.env.REACT_APP_AUTH0_DOMAIN!;
  const clientId = process.env.REACT_APP_AUTH0_CLIENT_ID!;
  const redirectUri = window.location.origin;
  const audience = process.env.REACT_APP_AUTH0_AUDIENCE!;

  const onRedirectCallback = (appState?: AppState) => {
    // Allow appState to be undefined
    navigate(appState?.returnTo || window.location.pathname);
  };

  return (
    <Auth0Provider
      authorizationParams={{
        redirect_uri: redirectUri,
        audience: audience,
      }}
      clientId={clientId}
      domain={domain}
      onRedirectCallback={onRedirectCallback} // Pass the updated onRedirectCallback
    >
      {children}
    </Auth0Provider>
  );
}
