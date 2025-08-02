import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateEmployeeRoleRequest,
  CreateEmployeeRoleResponse,
} from "@/modules/hub/features/EmployeeRoleManagement/api/createEmployeeRole/types";
import { GetEditEmployeeRoleResponse } from "@/modules/hub/features/EmployeeRoleManagement/api/getEditEmployeeRole/types";
import {
  UpdateEmployeeRoleRequest,
  UpdateEmployeeRoleResponse,
} from "@/modules/hub/features/EmployeeRoleManagement/api/updateEmployeeRole/types";
import {
  EmployeeRoleDTO,
  GetEmployeeRolesRequest,
  GetEmployeeRolesResponse,
} from "@/modules/hub/features/EmployeeRoleManagement/api/getEmployeeRoles/types";
import { createEmployeeRole } from "@/modules/hub/features/EmployeeRoleManagement/api/createEmployeeRole/createEmployeeRoleApi";
import { getEditEmployeeRole } from "@/modules/hub/features/EmployeeRoleManagement/api/getEditEmployeeRole/getEditEmployeeRoleApi";
import { updateEmployeeRole } from "@/modules/hub/features/EmployeeRoleManagement/api/updateEmployeeRole/updateEmployeeRoleApi";
import { deleteEmployeeRole } from "@/modules/hub/features/EmployeeRoleManagement/api/deleteEmployeeRole/deleteEmployeeRoleApi";
import { getEmployeeRoles } from "@/modules/hub/features/EmployeeRoleManagement/api/getEmployeeRoles/getEmployeeRolesApi";

interface EmployeeRoleState {
  loadingCreate: boolean;
  createError: string | null;
  createdRole: CreateEmployeeRoleResponse["data"] | null;

  loadingGet: boolean;
  getError: string | null;
  fetchedRole: GetEditEmployeeRoleResponse["data"] | null;

  loadingUpdate: boolean;
  updateError: string | null;
  updatedRole: UpdateEmployeeRoleResponse["data"] | null;

  loadingDelete: boolean;
  deleteError: string | null;
  deletedRoleId: number | null;

  loadingList: boolean;
  listError: string | null;
  rolesList: EmployeeRoleDTO[];
  totalCount: number;
}

const initialState: EmployeeRoleState = {
  loadingCreate: false,
  createError: null,
  createdRole: null,

  loadingGet: false,
  getError: null,
  fetchedRole: null,

  loadingUpdate: false,
  updateError: null,
  updatedRole: null,

  loadingDelete: false,
  deleteError: null,
  deletedRoleId: null,

  loadingList: false,
  listError: null,
  rolesList: [],
  totalCount: 0,
};

const employeeRoleSlice = createSlice({
  name: "employeeRole",
  initialState,
  reducers: {
    setLoadingCreate(state, action: PayloadAction<boolean>) {
      state.loadingCreate = action.payload;
    },
    setCreateError(state, action: PayloadAction<string | null>) {
      state.createError = action.payload;
    },
    setCreatedRole(
      state,
      action: PayloadAction<CreateEmployeeRoleResponse["data"] | null>,
    ) {
      state.createdRole = action.payload;
    },

    setLoadingGet(state, action: PayloadAction<boolean>) {
      state.loadingGet = action.payload;
    },
    setGetError(state, action: PayloadAction<string | null>) {
      state.getError = action.payload;
    },
    setFetchedRole(
      state,
      action: PayloadAction<GetEditEmployeeRoleResponse["data"] | null>,
    ) {
      state.fetchedRole = action.payload;
    },

    setLoadingUpdate(state, action: PayloadAction<boolean>) {
      state.loadingUpdate = action.payload;
    },
    setUpdateError(state, action: PayloadAction<string | null>) {
      state.updateError = action.payload;
    },
    setUpdatedRole(
      state,
      action: PayloadAction<UpdateEmployeeRoleResponse["data"] | null>,
    ) {
      state.updatedRole = action.payload;
    },

    setLoadingDelete(state, action: PayloadAction<boolean>) {
      state.loadingDelete = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    setDeletedRoleId(state, action: PayloadAction<number | null>) {
      state.deletedRoleId = action.payload;
    },

    setLoadingList(state, action: PayloadAction<boolean>) {
      state.loadingList = action.payload;
    },
    setListError(state, action: PayloadAction<string | null>) {
      state.listError = action.payload;
    },
    setRolesList(state, action: PayloadAction<EmployeeRoleDTO[]>) {
      state.rolesList = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },

    resetEmployeeRoleState(state) {
      state.loadingCreate = false;
      state.createError = null;
      state.createdRole = null;

      state.loadingGet = false;
      state.getError = null;
      state.fetchedRole = null;

      state.loadingUpdate = false;
      state.updateError = null;
      state.updatedRole = null;

      state.loadingDelete = false;
      state.deleteError = null;
      state.deletedRoleId = null;

      state.loadingList = false;
      state.listError = null;
      state.rolesList = [];
      state.totalCount = 0;
    },
  },
});

export const {
  setLoadingCreate,
  setCreateError,
  setCreatedRole,

  setLoadingGet,
  setGetError,
  setFetchedRole,

  setLoadingUpdate,
  setUpdateError,
  setUpdatedRole,

  setLoadingDelete,
  setDeleteError,
  setDeletedRoleId,

  setLoadingList,
  setListError,
  setRolesList,
  setTotalCount,

  resetEmployeeRoleState,
} = employeeRoleSlice.actions;

export default employeeRoleSlice.reducer;

export const createEmployeeRoleAction =
  (
    requestData: CreateEmployeeRoleRequest,
    onSuccess?: (roleData: CreateEmployeeRoleResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));
    try {
      const response = await createEmployeeRole(requestData);
      dispatch(setCreatedRole(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create the employee role.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const getEditEmployeeRoleAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setGetError(null));
    try {
      const response = await getEditEmployeeRole(id);
      dispatch(setFetchedRole(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch the employee role.";
      dispatch(setGetError(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const updateEmployeeRoleAction =
  (
    id: number,
    requestData: UpdateEmployeeRoleRequest,
    onSuccess?: (updatedData: UpdateEmployeeRoleResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));
    try {
      const response = await updateEmployeeRole(id, requestData);
      dispatch(setUpdatedRole(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update the employee role.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteEmployeeRoleAction =
  (id: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    dispatch(setDeleteError(null));
    try {
      await deleteEmployeeRole(id);
      dispatch(setDeletedRoleId(id));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete the employee role.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export const getEmployeeRolesAction =
  (
    params: GetEmployeeRolesRequest,
    onSuccess?: (rolesData: GetEmployeeRolesResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingList(true));
    dispatch(setListError(null));
    try {
      const response = await getEmployeeRoles(params);
      const { roles, totalCount } = response.data;
      dispatch(setRolesList(roles));
      dispatch(setTotalCount(totalCount));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch employee roles.";
      dispatch(setListError(message));
    } finally {
      dispatch(setLoadingList(false));
    }
  };
