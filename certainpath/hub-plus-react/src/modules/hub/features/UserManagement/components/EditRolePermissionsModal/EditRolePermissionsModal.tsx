import React, { Fragment, useState } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "../../../../../../app/rootReducer";
import {
  EditRolesAndPermissionsState,
  updateBusinessRolePermissionsAction,
} from "../../slices/editRolesAndPermissionsSlice";

interface EditRolePermissionsModalProps {
  roleId: number;
  onClose: () => void;
}

const EditRolePermissionsModal: React.FC<EditRolePermissionsModalProps> = ({
  roleId,
  onClose,
}) => {
  const dispatch = useDispatch();

  const { rolesAndPermissions } = useSelector<
    RootState,
    EditRolesAndPermissionsState
  >((state) => state.editRolesAndPermissions);

  const role = rolesAndPermissions?.roles.find((r) => r.id === roleId);

  // All permissions
  const allPermissions = rolesAndPermissions?.permissions || [];

  // Filter out any "certainPathOnly" permissions if the role is NOT "certainPathOnly"
  const filteredPermissions = role?.isCertainPathOnly
    ? allPermissions
    : allPermissions.filter((p) => !p.isCertainPathOnly);

  // Create a state of selected permission IDs (based on existing role permissions)
  const [selectedPermissionIds, setSelectedPermissionIds] = useState<number[]>(
    role?.permissions.map((p) => p.id) || [],
  );

  const handleCheckboxChange = (permissionId: number) => {
    setSelectedPermissionIds((prevSelected) => {
      if (prevSelected.includes(permissionId)) {
        return prevSelected.filter((id) => id !== permissionId);
      } else {
        return [...prevSelected, permissionId];
      }
    });
  };

  const handleSave = () => {
    dispatch(
      updateBusinessRolePermissionsAction(
        {
          roleId,
          permissionIds: selectedPermissionIds,
        },
        onClose,
      ),
    );
  };

  return (
    <Transition.Root as={Fragment} show={true}>
      <Dialog as="div" className="relative z-50" onClose={onClose}>
        {/* Background overlay */}
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </Transition.Child>

        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <Transition.Child
              as={Fragment}
              enter="ease-out duration-300"
              enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enterTo="opacity-100 translate-y-0 sm:scale-100"
              leave="ease-in duration-200"
              leaveFrom="opacity-100 translate-y-0 sm:scale-100"
              leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
              <Dialog.Panel className="relative transform overflow-hidden rounded-lg bg-white px-6 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                <div>
                  <Dialog.Title className="text-lg font-medium text-gray-900">
                    Edit Permissions for {role?.label}
                  </Dialog.Title>

                  <div className="mt-4 max-h-96 overflow-y-auto">
                    <fieldset className="space-y-4">
                      {filteredPermissions.map((permission) => (
                        <div
                          className="relative flex items-start"
                          key={permission.id}
                        >
                          <div className="flex items-center h-5">
                            <input
                              checked={selectedPermissionIds.includes(
                                permission.id,
                              )}
                              className="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                              id={`permission-${permission.id}`}
                              name={`permission-${permission.id}`}
                              onChange={() =>
                                handleCheckboxChange(permission.id)
                              }
                              type="checkbox"
                            />
                          </div>
                          <div className="ml-3 text-sm">
                            <label
                              className="font-medium text-gray-700"
                              htmlFor={`permission-${permission.id}`}
                            >
                              {permission.label}
                              {permission.isCertainPathOnly && (
                                <span className="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">
                                  Certain Path Only
                                </span>
                              )}
                            </label>
                            <p className="text-gray-500">
                              {permission.description}
                            </p>
                          </div>
                        </div>
                      ))}
                    </fieldset>
                  </div>

                  <div className="mt-6 flex justify-end space-x-3">
                    <button
                      className="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 border border-gray-300"
                      onClick={onClose}
                    >
                      Cancel
                    </button>
                    <button
                      className="rounded-md bg-secondary dark:bg-primary px-4 py-2 text-sm font-medium text-white dark:hover:bg-primary-light hover:bg-secondary-light"
                      onClick={handleSave}
                    >
                      Save
                    </button>
                  </div>
                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition.Root>
  );
};

export default EditRolePermissionsModal;
