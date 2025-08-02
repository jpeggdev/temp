import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { EditRolesAndPermissions } from "../../../../../api/getEditRolesAndPermissions/types";
import { getEditRolesAndPermissions } from "../../../../../api/getEditRolesAndPermissions/getEditRolesAndPermissionsApi";
import { UpdateBusinessRolePermissionRequest } from "../../../../../api/updateBusinessRolePermission/types";
import { updateBusinessRolePermissions } from "../../../../../api/updateBusinessRolePermission/updateBusinessRolePermissionApi";

export interface EditRolesAndPermissionsState {
  rolesAndPermissions: EditRolesAndPermissions | null;
  loading: boolean;
  error: string | null;
}

const initialState: EditRolesAndPermissionsState = {
  rolesAndPermissions: null,
  loading: false,
  error: null,
};

const editRolesAndPermissionsSlice = createSlice({
  name: "editRolesAndPermissions",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setRolesAndPermissions: (
      state,
      action: PayloadAction<EditRolesAndPermissions>,
    ) => {
      state.rolesAndPermissions = action.payload;
    },
    updateRolePermissionsInState: (
      state,
      action: PayloadAction<{
        roleId: number;
        permissionIds: number[];
      }>,
    ) => {
      const { roleId, permissionIds } = action.payload;

      if (state.rolesAndPermissions) {
        const roleIndex = state.rolesAndPermissions.roles.findIndex(
          (role) => role.id === roleId,
        );

        if (roleIndex !== -1) {
          const updatedPermissions =
            state.rolesAndPermissions.permissions.filter((permission) =>
              permissionIds.includes(permission.id),
            );

          state.rolesAndPermissions.roles[roleIndex].permissions =
            updatedPermissions;
        }
      }
    },
  },
});

export const {
  setLoading,
  setError,
  setRolesAndPermissions,
  updateRolePermissionsInState,
} = editRolesAndPermissionsSlice.actions;

// Thunk to fetch roles and permissions
export const fetchRolesAndPermissionsAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response = await getEditRolesAndPermissions();
      dispatch(setRolesAndPermissions(response.data));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch roles and permissions",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

// Thunk to update role permissions
export const updateBusinessRolePermissionsAction =
  (
    requestData: UpdateBusinessRolePermissionRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      await updateBusinessRolePermissions(requestData);
      dispatch(
        updateRolePermissionsInState({
          roleId: requestData.roleId,
          permissionIds: requestData.permissionIds,
        }),
      );
      if (callback) {
        callback();
      }
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to update business role permissions",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default editRolesAndPermissionsSlice.reducer;
