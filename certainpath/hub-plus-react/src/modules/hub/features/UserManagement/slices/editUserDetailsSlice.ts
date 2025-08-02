import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { EditUserDetails } from "../../../../../api/getEditUserDetails/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { getEditUserDetails } from "../../../../../api/getEditUserDetails/getEditUserDetailsApi";
import { UpdateEmployeeApplicationAccessRequest } from "../../../../../api/updateEmployeeApplicationAccess/types";
import { updateEmployeeApplicationAccess } from "../../../../../api/updateEmployeeApplicationAccess/updateEmployeeApplicationAccessApi";
import { UpdateEmployeeBusinessRoleRequest } from "../../../../../api/updateEmployeeBusinessRole/types";
import { updateEmployeeBusinessRole } from "../../../../../api/updateEmployeeBusinessRole/updateEmployeeBusinessRoleApi";
import { UpdateEmployeePermissionRequest } from "../../../../../api/updateEmployeePermission/types";
import { updateEmployeePermission } from "../../../../../api/updateEmployeePermission/updateEmployeePermissionApi";

interface EditUserDetailsState {
  userEditDetails: EditUserDetails | null;
  loading: boolean;
  error: string | null;
}

const initialState: EditUserDetailsState = {
  userEditDetails: null,
  loading: false,
  error: null,
};

const editUserDetailsSlice = createSlice({
  name: "editUserDetails",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setUserEditDetails: (state, action: PayloadAction<EditUserDetails>) => {
      state.userEditDetails = action.payload;
    },
    setEmployeePermissions: (
      state,
      action: PayloadAction<{
        employeeRolePermissions: number[];
        employeeAdditionalPermissions: number[];
      }>,
    ) => {
      if (state.userEditDetails) {
        state.userEditDetails.employeeRolePermissions =
          action.payload.employeeRolePermissions;
        state.userEditDetails.employeeAdditionalPermissions =
          action.payload.employeeAdditionalPermissions;
      }
    },
    updateEmployeePermissionInState: (
      state,
      action: PayloadAction<{ permissionId: number; active: boolean }>,
    ) => {
      const { permissionId, active } = action.payload;

      if (state.userEditDetails) {
        if (active) {
          state.userEditDetails.employeeAdditionalPermissions.push(
            permissionId,
          );
        } else {
          state.userEditDetails.employeeAdditionalPermissions =
            state.userEditDetails.employeeAdditionalPermissions.filter(
              (id) => id !== permissionId,
            );
        }
      }
    },
    updateEmployeeBusinessRoleInState: (
      state,
      action: PayloadAction<{ businessRoleId: number }>,
    ) => {
      if (state.userEditDetails) {
        state.userEditDetails.employeeBusinessRoleId =
          action.payload.businessRoleId;
      }
    },
    updateEmployeeApplicationAccessInState: (
      state,
      action: PayloadAction<{ applicationId: number; active: boolean }>,
    ) => {
      const { applicationId, active } = action.payload;

      if (state.userEditDetails) {
        // Check if the access already exists
        const existingAccess =
          state.userEditDetails.employeeApplicationAccess.find(
            (access) => access.applicationId === applicationId,
          );

        if (active) {
          // Add if not already present
          if (!existingAccess) {
            state.userEditDetails.employeeApplicationAccess.push({
              applicationId,
              applicationName: "", // Update as needed
            });
          }
        } else {
          // Remove if exists
          state.userEditDetails.employeeApplicationAccess =
            state.userEditDetails.employeeApplicationAccess.filter(
              (access) => access.applicationId !== applicationId,
            );
        }
      }
    },
  },
});

export const {
  setLoading,
  setError,
  setUserEditDetails,
  updateEmployeePermissionInState,
  setEmployeePermissions,
  updateEmployeeBusinessRoleInState,
  updateEmployeeApplicationAccessInState,
} = editUserDetailsSlice.actions;

export const fetchUserEditDetailsAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const userDetails = await getEditUserDetails(uuid);
      dispatch(setUserEditDetails(userDetails.data));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch user details",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const updateEmployeeApplicationAccessAction =
  (
    uuid: string,
    requestData: UpdateEmployeeApplicationAccessRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      await updateEmployeeApplicationAccess(uuid, requestData);
      dispatch(
        updateEmployeeApplicationAccessInState({
          applicationId: requestData.applicationId,
          active: requestData.active,
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
            : "Failed to update application access",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const updateEmployeeBusinessRoleAction =
  (
    uuid: string,
    requestData: UpdateEmployeeBusinessRoleRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      await updateEmployeeBusinessRole(uuid, requestData);
      dispatch(
        updateEmployeeBusinessRoleInState({
          businessRoleId: requestData.businessRoleId,
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
            : "Failed to update business role",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const updateEmployeePermissionAction =
  (
    uuid: string,
    requestData: UpdateEmployeePermissionRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      await updateEmployeePermission(uuid, requestData);
      dispatch(
        updateEmployeePermissionInState({
          permissionId: requestData.permissionId,
          active: requestData.active,
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
            : "Failed to update employee permission",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const refreshUserPermissionsAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const userDetails = await getEditUserDetails(uuid);
      const { employeeRolePermissions, employeeAdditionalPermissions } =
        userDetails.data;
      dispatch(
        setEmployeePermissions({
          employeeRolePermissions,
          employeeAdditionalPermissions,
        }),
      );
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch user permissions",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default editUserDetailsSlice.reducer;
