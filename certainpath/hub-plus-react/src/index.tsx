import React from "react";
import ReactDOM from "react-dom/client";
import "./index.css";
import App from "./App";
import { ErrorBoundary } from "./utils/bugsnag";
import reportWebVitals from "./reportWebVitals";
import { Provider } from "react-redux";
import store from "./app/store";
import { BrowserRouter } from "react-router-dom";
import { HelmetProvider } from "react-helmet-async";
import HotglueConfig from "@hotglue/widget";
import { Auth0ProviderWithNavigate } from "./components/Auth0ProviderWithNavigate/Auth0ProviderWithNavigate";
import { NotificationProvider } from "./context/NotificationContext";
import Gleap from "gleap";
import { ApolloProvider } from "@apollo/client";
import client from "./services/apolloClient";
Gleap.initialize(process.env.REACT_APP_GLEAP_API_KEY || "");

function loadAuthorizeNetScript() {
  const isProduction = process.env.REACT_APP_ENVIRONMENT === "production";

  const scriptUrl = isProduction
    ? "https://js.authorize.net/v1/Accept.js"
    : "https://jstest.authorize.net/v1/Accept.js";

  const script = document.createElement("script");
  script.type = "text/javascript";
  script.src = scriptUrl;
  script.charset = "utf-8";

  document.body.appendChild(script);
}

loadAuthorizeNetScript();

const root = ReactDOM.createRoot(
  document.getElementById("root") as HTMLElement,
);

root.render(
  <React.StrictMode>
    <ApolloProvider client={client}>
      <ErrorBoundary>
        <Provider store={store}>
          <BrowserRouter>
            <HelmetProvider>
              <Auth0ProviderWithNavigate>
                <HotglueConfig
                  config={{
                    apiKey: "Z0DhQ4F5Th9UvKqSL2OLd9DvP47PUX3G2DMKXjKf",
                    envId: "dev.hg.mycertainpath.com",
                  }}
                >
                  <NotificationProvider>
                    <App />
                  </NotificationProvider>
                </HotglueConfig>
              </Auth0ProviderWithNavigate>
            </HelmetProvider>
          </BrowserRouter>
        </Provider>
      </ErrorBoundary>
    </ApolloProvider>
  </React.StrictMode>,
);

/**
 * If you want to start measuring performance in your app,
 * pass a function to log or send results (for example: console.log),
 * or send to an analytics endpoint.
 * Learn more: https://create-react-app.dev/docs/measuring-performance/#measuring-performance
 */
reportWebVitals(console.log);
