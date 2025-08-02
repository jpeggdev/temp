import React, { useEffect } from "react";
import { useAuth0 } from "@auth0/auth0-react";

const Logout: React.FC = () => {
  const { logout } = useAuth0();

  useEffect(() => {
    localStorage.removeItem("selectedCompanyUuid");
    localStorage.removeItem("impersonateUserUuid");

    logout({
      logoutParams: {
        returnTo: window.location.origin,
      },
    });
  }, [logout]);

  return <div>Logging out...</div>;
};

export default Logout;
