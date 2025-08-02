import axios, { AxiosInstance } from "axios";
import { GetTokenSilentlyOptions } from "@auth0/auth0-react";
import { GetTokenSilentlyVerboseResponse } from "@auth0/auth0-spa-js/dist/typings/global";
import { fetchMyCompany } from "./fetchMyCompany/fetchMyCompanyApi";
import { extractErrorMessage } from "@/utils/extractErrorMessage";

const axiosInstance: AxiosInstance = axios.create({
  baseURL: process.env.REACT_APP_API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
  },
  withCredentials: true,
});

export const axiosInstanceWithoutInterceptors: AxiosInstance = axios.create({
  baseURL: process.env.REACT_APP_API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
  },
  withCredentials: true,
});

export const addAccessTokenInterceptor = (
  axiosInstance: AxiosInstance,
  getAccessTokenSilently: {
    (
      options: GetTokenSilentlyOptions & { detailedResponse: true },
    ): Promise<GetTokenSilentlyVerboseResponse>;
    (options?: GetTokenSilentlyOptions): Promise<string>;
    (
      options: GetTokenSilentlyOptions,
    ): Promise<GetTokenSilentlyVerboseResponse | string>;
  },
  loginWithRedirect: () => Promise<void>,
  showNotification: (
    message: string,
    description: string,
    type: "success" | "error",
  ) => void,
) => {
  axiosInstance.interceptors.request.use(
    async (config) => {
      try {
        const token = await getAccessTokenSilently({
          authorizationParams: {
            audience: process.env.REACT_APP_AUTH0_AUDIENCE,
          },
        });
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
      } catch (error) {
        console.error("Error getting access token:", error);
      }

      return config;
    },
    (error) => {
      return Promise.reject(error);
    },
  );

  axiosInstance.interceptors.response.use(
    (response) => response,
    async (error) => {
      if (error.response?.status === 401) {
        console.log("Unauthorized! Redirecting to login...");
        await loginWithRedirect();
      } else if (error.response?.status === 403) {
        console.log("Forbidden! Redirecting to /403 page...");
        window.location.assign("/403");
      } else {
        try {
          const errorMessage = await extractErrorMessage(error.response);
          showNotification("Error", errorMessage, "error");
        } catch (parseError) {
          console.error("Failed to extract error message:", parseError);
          showNotification("Error", "An error occurred", "error");
        }
      }

      return Promise.reject(error);
    },
  );
};

export const addCompanyUuidInterceptor = (
  axiosInstance: AxiosInstance,
  getAccessTokenSilently: {
    (
      options: GetTokenSilentlyOptions & { detailedResponse: true },
    ): Promise<GetTokenSilentlyVerboseResponse>;
    (options?: GetTokenSilentlyOptions): Promise<string>;
    (
      options: GetTokenSilentlyOptions,
    ): Promise<GetTokenSilentlyVerboseResponse | string>;
  },
) => {
  const fetchCompanyUuid = async () => {
    try {
      const token = await getAccessTokenSilently({
        authorizationParams: {
          audience: process.env.REACT_APP_AUTH0_AUDIENCE,
        },
      });

      const myCompanyResponse = await fetchMyCompany(token);
      const companyUuid = myCompanyResponse.data.uuid;

      if (companyUuid) {
        localStorage.setItem("selectedCompanyUuid", companyUuid);
        return companyUuid;
      } else {
        console.error("No company UUID found");
        return null;
      }
    } catch (error) {
      console.error("Error fetching company UUID:", error);
      return null;
    }
  };

  axiosInstance.interceptors.request.use(
    async (config) => {
      let companyUuid = localStorage.getItem("selectedCompanyUuid");

      if (!companyUuid) {
        companyUuid = await fetchCompanyUuid();
      }

      if (companyUuid) {
        config.headers["X-Company-UUID"] = companyUuid;
      } else {
        console.error("Company UUID is missing and could not be retrieved.");
      }

      if (
        process.env.REACT_APP_API_DEBUG === "true" &&
        config.url &&
        !config.url.includes("XDEBUG_SESSION_START=PHPSTORM")
      ) {
        const separator = config.url.includes("?") ? "&" : "?";
        config.url += `${separator}XDEBUG_SESSION_START=PHPSTORM`;
      }

      return config;
    },
    (error) => {
      return Promise.reject(error);
    },
  );
};

export const addImpersonateUserInterceptor = (axiosInstance: AxiosInstance) => {
  axiosInstance.interceptors.request.use(
    (config) => {
      const impersonateUserUuid = localStorage.getItem("impersonateUserUuid");

      if (impersonateUserUuid) {
        config.headers["X-Impersonate-User-UUID"] = impersonateUserUuid;
        console.log(`Impersonating user: ${impersonateUserUuid}`);
      }

      return config;
    },
    (error) => {
      return Promise.reject(error);
    },
  );
};

export { axiosInstance };
export default axiosInstance;
