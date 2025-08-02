import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { fetchEmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/fetchEmailTemplateApi";
import { EmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/types";
import { copyEmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/copyEmailTemplate/fetchEmailTemplateApi";
import {
  UpdateEmailTemplateRequest,
  UpdateEmailTemplateResponse,
} from "@/modules/emailManagement/features/EmailTemplateManagement/api/updateEmailTemplate/types";
import { CreateEmailTemplateRequest } from "@/modules/emailManagement/features/EmailTemplateManagement/api/createEmailTemplate/types";
import { createEmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/createEmailTemplate/createEmailTemplateApi";
import { updateEmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/updateEmailTemplate/updateEmailTemplateApi";
import { deleteEmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/deleteEmailTemplate/deleteEmailTemplateApi";

interface EmailTemplateState {
  fetchedEmailTemplate: EmailTemplate | null;
  updatedEmailTemplate: EmailTemplate | null;
  fetchLoading: boolean;
  createLoading: boolean;
  updateLoading: boolean;
  duplicateLoading: boolean;
  deleteLoading: boolean;
  fetchError: string | null;
  updateError: string | null;
  duplicateError: string | null;
  deleteError: string | null;
}

const initialState: EmailTemplateState = {
  fetchedEmailTemplate: null,
  updatedEmailTemplate: null,
  fetchLoading: false,
  createLoading: false,
  updateLoading: false,
  duplicateLoading: false,
  deleteLoading: false,
  fetchError: null,
  updateError: null,
  duplicateError: null,
  deleteError: null,
};

const emailTemplateSlice = createSlice({
  name: "emailTemplate",
  initialState,
  reducers: {
    setFetchedEmailTemplate: (state, action: PayloadAction<EmailTemplate>) => {
      state.fetchedEmailTemplate = action.payload;
    },
    setUpdatedEmailTemplate: (state, action: PayloadAction<EmailTemplate>) => {
      state.updatedEmailTemplate = action.payload;
    },
    setFetchLoading: (state, action: PayloadAction<boolean>) => {
      state.fetchLoading = action.payload;
    },
    setCreateLoading: (state, action: PayloadAction<boolean>) => {
      state.createLoading = action.payload;
    },
    setUpdateLoading: (state, action: PayloadAction<boolean>) => {
      state.updateLoading = action.payload;
    },
    setDuplicateLoading: (state, action: PayloadAction<boolean>) => {
      state.duplicateLoading = action.payload;
    },
    setDeleteLoading: (state, action: PayloadAction<boolean>) => {
      state.deleteLoading = action.payload;
    },
    setFetchError: (state, action: PayloadAction<string | null>) => {
      state.fetchError = action.payload;
    },
    setCreateError: (state, action: PayloadAction<string | null>) => {
      state.fetchError = action.payload;
    },
    setUpdateError: (state, action: PayloadAction<string | null>) => {
      state.updateError = action.payload;
    },
    setDuplicateError: (state, action: PayloadAction<string | null>) => {
      state.duplicateError = action.payload;
    },
    setDeleteError: (state, action: PayloadAction<string | null>) => {
      state.deleteError = action.payload;
    },
  },
});

export const {
  setFetchedEmailTemplate,
  setUpdatedEmailTemplate,
  setFetchLoading,
  setCreateLoading,
  setUpdateLoading,
  setDuplicateLoading,
  setDeleteLoading,
  setFetchError,
  setCreateError,
  setUpdateError,
  setDuplicateError,
  setDeleteError,
} = emailTemplateSlice.actions;

export const fetchEmailTemplateAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setFetchLoading(true));
    try {
      const response = await fetchEmailTemplate(id);
      dispatch(setFetchedEmailTemplate(response.data));
    } catch (error) {
      dispatch(
        setFetchError(
          error instanceof Error
            ? error.message
            : "Failed to fetch the email template",
        ),
      );
    } finally {
      dispatch(setFetchLoading(false));
    }
  };

export const createEmailTemplateAction =
  (requestData: CreateEmailTemplateRequest, callback?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setCreateLoading(true));
    dispatch(setCreateError(null));
    try {
      await createEmailTemplate(requestData);
      dispatch(setCreateError(null));
      if (callback) {
        callback();
      }
    } catch (error) {
      dispatch(
        setCreateError(
          error instanceof Error
            ? error.message
            : "Failed to create the email template",
        ),
      );
    } finally {
      dispatch(setCreateLoading(false));
    }
  };

export const updateEmailTemplateAction =
  (
    id: number,
    requestData: UpdateEmailTemplateRequest,
    onSuccess?: (updatedData: UpdateEmailTemplateResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setUpdateLoading(true));
    dispatch(setUpdateError(null));
    try {
      const response = await updateEmailTemplate(id, requestData);
      dispatch(setUpdatedEmailTemplate(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update the email template.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setUpdateLoading(false));
    }
  };

export const duplicateEmailTemplateAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDuplicateLoading(true));
    try {
      await copyEmailTemplate(id);
    } catch (error) {
      dispatch(
        setDuplicateError(
          error instanceof Error
            ? error.message
            : "Failed to duplicate the email template.",
        ),
      );
    } finally {
      dispatch(setDuplicateLoading(false));
    }
  };

export const deleteEmailTemplateAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    try {
      await deleteEmailTemplate(id);
    } catch (error) {
      dispatch(
        setDeleteError(
          error instanceof Error
            ? error.message
            : "Failed to delete the email template",
        ),
      );
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };

export default emailTemplateSlice.reducer;
