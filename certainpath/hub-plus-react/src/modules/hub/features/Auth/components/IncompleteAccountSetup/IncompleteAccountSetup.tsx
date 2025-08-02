import React from "react";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";

const IncompleteAccountSetup: React.FC = () => {
  return (
    <MainPageWrapper
      error={null}
      loading={false}
      title="Account Setup Incomplete"
    >
      <div className="text-center py-10">
        <h2 className="text-lg font-bold text-red-600">
          Account Setup Incomplete
        </h2>
        <p className="text-sm text-gray-600 mt-4">
          Your account is missing required roles or permissions to access the
          system. Please contact your company administrator to complete your
          account setup.
        </p>
      </div>
    </MainPageWrapper>
  );
};

export default IncompleteAccountSetup;
