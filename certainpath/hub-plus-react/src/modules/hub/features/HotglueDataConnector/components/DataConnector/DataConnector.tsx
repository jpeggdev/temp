import React, { useEffect } from "react";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { useSelector } from "react-redux";
import { selectIntacctId } from "../../../UserAppSettings/selectors/userAppSettingsSelectors";
import { Connections, useHotglue } from "@hotglue/widget";
import { useAuth0 } from "@auth0/auth0-react";

const DataConnector: React.FC = () => {
  const intacctId = useSelector(selectIntacctId);
  const { setMetadata } = useHotglue();
  const { user } = useAuth0();

  useEffect(() => {
    if (intacctId && user) {
      setMetadata({
        application: "Hub Plus",
        ssoId: user.sub,
      });
    }
  }, [intacctId, user, setMetadata]);

  if (!intacctId) {
    return null;
  }

  return (
    <MainPageWrapper error={null} loading={!intacctId} title="Data Connector">
      <Connections tenant={intacctId} />
    </MainPageWrapper>
  );
};

export default DataConnector;
