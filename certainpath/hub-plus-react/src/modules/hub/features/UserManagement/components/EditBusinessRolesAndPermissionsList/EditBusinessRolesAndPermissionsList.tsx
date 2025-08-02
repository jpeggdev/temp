import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { RootState } from "../../../../../../app/rootReducer";
import {
  EditRolesAndPermissionsState,
  fetchRolesAndPermissionsAction,
} from "../../slices/editRolesAndPermissionsSlice";
import EditRolePermissionsModal from "../EditRolePermissionsModal/EditRolePermissionsModal";

const EditBusinessRolesAndPermissionsList: React.FC = () => {
  const dispatch = useDispatch();

  const { rolesAndPermissions, loading, error } = useSelector<
    RootState,
    EditRolesAndPermissionsState
  >((state) => state.editRolesAndPermissions);

  const [selectedRoleId, setSelectedRoleId] = useState<number | null>(null);
  const [modalOpen, setModalOpen] = useState<boolean>(false);

  useEffect(() => {
    dispatch(fetchRolesAndPermissionsAction());
  }, [dispatch]);

  const handleEditPermissions = (roleId: number) => {
    setSelectedRoleId(roleId);
    setModalOpen(true);
  };

  const handleModalClose = () => {
    setModalOpen(false);
    setSelectedRoleId(null);
  };

  return (
    <MainPageWrapper error={error} loading={loading} title="Business Roles">
      <div className="p-6">
        <h1 className="text-2xl font-bold">Business Roles</h1>
        <ul className="mt-6 space-y-4">
          {rolesAndPermissions?.roles.map((role) => (
            <li
              className="flex items-center justify-between p-4 border rounded-md"
              key={role.id}
            >
              <div>
                <h2 className="text-lg font-semibold flex items-center">
                  {role.label}
                  {role.isCertainPathOnly && (
                    <span className="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                      Certain Path Only
                    </span>
                  )}
                </h2>
                <p className="text-gray-500">{role.description}</p>
              </div>
              <button
                className="bg-secondary dark:bg-primary text-white px-4 py-2 rounded-md dark:hover:bg-primary-light hover:bg-secondary-light"
                onClick={() => handleEditPermissions(role.id)}
              >
                Edit Permissions
              </button>
            </li>
          ))}
        </ul>

        {selectedRoleId !== null && modalOpen && (
          <EditRolePermissionsModal
            onClose={handleModalClose}
            roleId={selectedRoleId}
          />
        )}
      </div>
    </MainPageWrapper>
  );
};

export default EditBusinessRolesAndPermissionsList;
