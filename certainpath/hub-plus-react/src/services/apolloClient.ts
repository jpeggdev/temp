import { ApolloClient, InMemoryCache, HttpLink, split } from "@apollo/client";
import { WebSocketLink } from "@apollo/client/link/ws";
import { getMainDefinition } from "@apollo/client/utilities";
import { SubscriptionClient } from "subscriptions-transport-ws";

const apiUrl = process.env.REACT_APP_HASURA_GRAPHQL_ENDPOINT;
const wsApiUrl = process.env.REACT_APP_HASURA_WS_GRAPHQL_ENDPOINT;
const apiKey = process.env.REACT_APP_HASURA_ADMIN_SECRET;

if (!apiUrl || !apiKey || !wsApiUrl) {
  throw new Error(
    "API URL or API Key is not defined in environment variables.",
  );
}

const httpLink = new HttpLink({
  uri: apiUrl,
  headers: {
    "x-hasura-admin-secret": apiKey,
  },
});

const wsLink = new WebSocketLink(
  new SubscriptionClient(wsApiUrl, {
    reconnect: true,
    connectionParams: {
      headers: {
        "x-hasura-admin-secret": apiKey,
      },
    },
  }),
);

const splitLink = split(
  ({ query }) => {
    const definition = getMainDefinition(query);
    return (
      definition.kind === "OperationDefinition" &&
      definition.operation === "subscription"
    );
  },
  wsLink,
  httpLink,
);

const client = new ApolloClient({
  link: splitLink,
  cache: new InMemoryCache(),
});

export default client;
