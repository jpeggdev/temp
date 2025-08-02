import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useParams } from "react-router-dom";
import { fetchUserEditDetailsAction } from "../../slices/editUserDetailsSlice";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { RootState } from "../../../../../../app/rootReducer";
import EditUserForm from "../EditUserForm/EditUserForm";
import ApplicationAccess from "../ApplicationAccess/ApplicationAccess";
import BusinessRole from "../BusinessRole/BusinessRole";
import Permissions from "../Permissions/Permissions";
import { useValidation } from "../../../../../../hooks/useValidation";
import { useNotification } from "../../../../../../context/NotificationContext";
import { editUserAction } from "../../slices/usersSlice";
import validationSchema from "../EditUserForm/validationSchema";
import clsx from "clsx";

interface FormValues {
  firstName: string;
  lastName: string;
}

const EditUser: React.FC = () => {
  const { uuid } = useParams<{ uuid: string }>();
  const dispatch = useDispatch();
  const { loading, error, userEditDetails } = useSelector(
    (state: RootState) => state.editUserDetails,
  );

  const { loading: saveLoading } = useSelector(
    (state: RootState) => state.users,
  );
  const [currentTab, setCurrentTab] = useState("User Info");
  const { showNotification } = useNotification();

  const { values, setValues, errors, handleChange, validateForm, isFormValid } =
    useValidation<FormValues>(
      { firstName: "", lastName: "" },
      validationSchema,
    );

  useEffect(() => {
    if (uuid) {
      dispatch(fetchUserEditDetailsAction(uuid));
    }
  }, [dispatch, uuid]);

  useEffect(() => {
    if (userEditDetails) {
      setValues({
        firstName: userEditDetails.firstName,
        lastName: userEditDetails.lastName,
      });
    }
  }, [userEditDetails, setValues]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm() && uuid) {
      try {
        await dispatch(
          editUserAction(uuid, {
            firstName: values.firstName,
            lastName: values.lastName,
          }),
        );
        showNotification(
          "Successfully updated user!",
          "The user information has been updated.",
          "success",
        );
      } catch {
        showNotification(
          "Failed to update user!",
          "There was an error updating the user.",
          "error",
        );
      }
    }
  };

  const tabs = [
    { name: "User Info", current: currentTab === "User Info" },
    {
      name: "Application Access",
      current: currentTab === "Application Access",
    },
    { name: "Business Role", current: currentTab === "Business Role" },
    { name: "Permissions", current: currentTab === "Permissions" },
  ];

  const manualBreadcrumbs = userEditDetails
    ? [
        { path: "/hub", label: "Hub Dashboard" },
        { path: "/hub/users", label: "Users" },
        {
          path: `/hub/users/${uuid}/edit`,
          label: `Edit User: ${userEditDetails.firstName} ${userEditDetails.lastName}`,
          clickable: false,
        },
      ]
    : undefined;

  if (!uuid) {
    return null;
  }

  return (
    <MainPageWrapper
      error={error}
      loading={loading || saveLoading}
      manualBreadcrumbs={manualBreadcrumbs}
      title="Edit User"
    >
      {/* Tabs for smaller screens */}
      <div className="sm:hidden">
        <select
          className="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
          id="tabs"
          name="tabs"
          onChange={(e) => setCurrentTab(e.target.value)}
          value={currentTab}
        >
          {tabs.map((tab) => (
            <option key={tab.name} value={tab.name}>
              {tab.name}
            </option>
          ))}
        </select>
      </div>
      {/* Tabs for larger screens */}
      <div className="hidden sm:block mb-10">
        <div className="border-b border-gray-200">
          <nav aria-label="Tabs" className="-mb-px flex space-x-8">
            {tabs.map((tab) => (
              <button
                aria-current={tab.current ? "page" : undefined}
                className={clsx(
                  tab.current
                    ? "border-indigo-500 text-indigo-600"
                    : "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700",
                  "whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium",
                )}
                key={tab.name}
                onClick={() => setCurrentTab(tab.name)}
              >
                {tab.name}
              </button>
            ))}
          </nav>
        </div>
      </div>
      {/* Render the appropriate tab content */}
      {currentTab === "User Info" && userEditDetails && (
        <EditUserForm
          errors={errors}
          handleChange={handleChange}
          handleSubmit={handleSubmit}
          isFormValid={isFormValid}
          values={values}
        />
      )}
      {currentTab === "Application Access" && userEditDetails && (
        <ApplicationAccess
          availableApplications={userEditDetails.availableApplications}
          employeeApplicationAccess={userEditDetails.employeeApplicationAccess}
          uuid={uuid}
        />
      )}
      {currentTab === "Business Role" && userEditDetails && (
        <BusinessRole
          availableRoles={userEditDetails.availableRoles}
          employeeBusinessRoleId={userEditDetails.employeeBusinessRoleId}
          uuid={uuid}
        />
      )}
      {currentTab === "Permissions" && userEditDetails && (
        <Permissions
          availablePermissionGroups={userEditDetails.availablePermissions}
          employeeAdditionalPermissions={
            userEditDetails.employeeAdditionalPermissions
          }
          employeeRolePermissions={userEditDetails.employeeRolePermissions}
          uuid={uuid}
        />
      )}
    </MainPageWrapper>
  );
};

export default EditUser;
