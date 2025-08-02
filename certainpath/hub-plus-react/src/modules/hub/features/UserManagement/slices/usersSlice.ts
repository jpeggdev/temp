import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  FetchUsersRequest,
  FetchUsersResponse,
} from "../../../../../api/fetchUsers/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { fetchUsers } from "../../../../../api/fetchUsers/fetchUsersApi";
import { editUser } from "../../../../../api/editUser/editUserApi";
import { createUser } from "../../../../../api/createUser/createUserApi";
import { RootState } from "@/app/rootReducer";

export interface User {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  uuid: string | null;
  employeeUuid: string | null;
  salesforceId?: string | null;
}

interface UsersState {
  users: User[];
  selectedUser: User | null;
  selectedUsers: User[];
  totalCount: number;
  loading: boolean;
  error: string | null;
  searchTerm: string;
}

const initialState: UsersState = {
  users: [],
  selectedUser: null,
  selectedUsers: [],
  totalCount: 0,
  loading: false,
  error: null,
  searchTerm: "",
};

const usersSlice = createSlice({
  name: "users",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setUsersData: (
      state,
      action: PayloadAction<{ users: User[]; totalCount: number }>,
    ) => {
      state.users = action.payload.users;
      state.totalCount = action.payload.totalCount;
    },
    setSelectedUser: (state, action: PayloadAction<User | null>) => {
      state.selectedUser = action.payload;
    },
    updateUser: (state, action: PayloadAction<User>) => {
      state.selectedUser = action.payload;
    },
    addUser: (state, action: PayloadAction<User>) => {
      state.users.push(action.payload);
    },
    toggleUserSelection: (state, action: PayloadAction<User>) => {
      const user = action.payload;
      const index = state.selectedUsers.findIndex(
        (selected) => selected.id === user.id,
      );
      if (index === -1) {
        state.selectedUsers.push(user);
      } else {
        state.selectedUsers.splice(index, 1);
      }
    },
    clearSelectedUsers: (state) => {
      state.selectedUsers = [];
    },
    setSelectedUsers: (state, action: PayloadAction<User[]>) => {
      state.selectedUsers = action.payload;
    },
    setUserSearchTerm: (state, action: PayloadAction<string>) => {
      state.searchTerm = action.payload;
    },
  },
});

export const {
  setLoading,
  setError,
  setUsersData,
  setSelectedUser,
  updateUser,
  addUser,
  toggleUserSelection,
  setSelectedUsers,
  setUserSearchTerm,
} = usersSlice.actions;

export const selectUsers = (state: RootState) => state.users.users;
export const selectSelectedUsers = (state: RootState) =>
  state.users.selectedUsers;
export const selectUserSearchTerm = (state: RootState) =>
  state.users.searchTerm;

export const fetchUsersAction =
  (requestData: FetchUsersRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchUsersResponse = await fetchUsers(requestData);
      dispatch(
        setUsersData({
          users: response.data.users,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error ? error.message : "Failed to fetch users",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const editUserAction =
  (
    uuid: string,
    requestData: { firstName: string; lastName: string },
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response = await editUser(uuid, requestData);
      const updatedUserFromApi = response.data;

      const updatedUser: User = {
        id: updatedUserFromApi.id,
        firstName: updatedUserFromApi.firstName,
        lastName: updatedUserFromApi.lastName,
        email: updatedUserFromApi.email,
        salesforceId: updatedUserFromApi.salesforceId || null,
        employeeUuid: null,
        uuid: null,
      };

      dispatch(updateUser(updatedUser));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error ? error.message : "Failed to edit user",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const createUserAction =
  (
    requestData: {
      firstName: string;
      lastName: string;
      email: string;
    },
    callback?: (newUser: User) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response = await createUser(requestData);
      const newUserFromApi = response.data;

      const newUser: User = {
        id: newUserFromApi.id,
        firstName: newUserFromApi.firstName,
        lastName: newUserFromApi.lastName,
        email: newUserFromApi.email,
        salesforceId: newUserFromApi.salesforceId || null,
        employeeUuid: newUserFromApi.employeeUuid || "default-uuid",
        uuid: null,
      };

      dispatch(addUser(newUser));

      if (callback) {
        callback(newUser);
      }
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error ? error.message : "Failed to create user",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default usersSlice.reducer;
