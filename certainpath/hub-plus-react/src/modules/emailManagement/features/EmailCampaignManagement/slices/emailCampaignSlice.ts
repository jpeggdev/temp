import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateEmailCampaignRequest,
  EmailCampaign,
} from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";
import { createEmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/createEmailCampaignApi";
import { fetchEmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaign/fetchEmailCampaignApi";
import { deleteEmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/deleteEmailCampaign/deleteEmailCampaignApi";
import { fetchEmailCampaignRecipientCount } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaignRecipientCount/fetchEmailCampaignRecipientCountApi";
import { EmailCampaignRecipientCount } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaignRecipientCount/types";
import { duplicateEmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/duplicateEmailCampaign/duplicateEmailCampaignApi";
import { updateEmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/updateEmailCampaign/updateEmailCampaignApi";
import { UpdateEmailCampaignRequest } from "@/modules/emailManagement/features/EmailCampaignManagement/api/updateEmailCampaign/types";

interface EmailCampaignState {
  createdEmailCampaign: EmailCampaign | null;
  loadingCreate: boolean;
  errorCreate: string | null;

  updatedEmailCampaign: EmailCampaign | null;
  loadingUpdate: boolean;
  errorUpdate: string | null;

  fetchedEmailCampaign: EmailCampaign | null;
  loadingFetch: boolean;
  errorFetch: string | null;

  deletedEmailCampaignId: number | null;
  loadingDelete: boolean;
  errorDelete: string | null;

  duplicatedEmailCampaignId: number | null;
  loadingDuplicate: boolean;
  errorDuplicate: string | null;

  recipientCount: EmailCampaignRecipientCount | null;
  loadingFetchRecipientCount: boolean;
  errorFetchRecipientCount: string | null;
}

const initialState: EmailCampaignState = {
  createdEmailCampaign: null,
  loadingCreate: false,
  errorCreate: null,

  updatedEmailCampaign: null,
  loadingUpdate: false,
  errorUpdate: null,

  fetchedEmailCampaign: null,
  loadingFetch: false,
  errorFetch: null,

  deletedEmailCampaignId: null,
  loadingDelete: false,
  errorDelete: null,

  duplicatedEmailCampaignId: null,
  loadingDuplicate: false,
  errorDuplicate: null,

  recipientCount: null,
  loadingFetchRecipientCount: false,
  errorFetchRecipientCount: null,
};

const emailCampaignSlice = createSlice({
  name: "emailCampaign",
  initialState,
  reducers: {
    setCreatedEmailCampaign: (
      state,
      action: PayloadAction<{
        data: EmailCampaign;
      }>,
    ) => {
      state.createdEmailCampaign = action.payload.data;
    },
    setLoadingCreate: (state, action: PayloadAction<boolean>) => {
      state.loadingCreate = action.payload;
    },
    setErrorCreate: (state, action: PayloadAction<string | null>) => {
      state.errorCreate = action.payload;
    },
    setFetchedEmailCampaign: (
      state,
      action: PayloadAction<{
        data: EmailCampaign;
      }>,
    ) => {
      state.fetchedEmailCampaign = action.payload.data;
    },
    setLoadingFetch: (state, action: PayloadAction<boolean>) => {
      state.loadingFetch = action.payload;
    },
    setErrorFetch: (state, action: PayloadAction<string | null>) => {
      state.errorFetch = action.payload;
    },
    setUpdatedEmailCampaign: (
      state,
      action: PayloadAction<{
        data: EmailCampaign;
      }>,
    ) => {
      state.updatedEmailCampaign = action.payload.data;
    },
    setLoadingUpdate: (state, action: PayloadAction<boolean>) => {
      state.loadingDelete = action.payload;
    },
    setErrorUpdate: (state, action: PayloadAction<string | null>) => {
      state.errorDelete = action.payload;
    },
    setDeletedEmailCampaignId(state, action: PayloadAction<number | null>) {
      state.deletedEmailCampaignId = action.payload;
    },
    setLoadingDelete: (state, action: PayloadAction<boolean>) => {
      state.loadingDelete = action.payload;
    },
    setErrorDelete: (state, action: PayloadAction<string | null>) => {
      state.errorDelete = action.payload;
    },
    setDuplicatedEmailCampaignId(state, action: PayloadAction<number | null>) {
      state.duplicatedEmailCampaignId = action.payload;
    },
    setLoadingDuplicate: (state, action: PayloadAction<boolean>) => {
      state.loadingDelete = action.payload;
    },
    setErrorDuplicate: (state, action: PayloadAction<string | null>) => {
      state.errorDelete = action.payload;
    },
    setRecipientCount: (
      state,
      action: PayloadAction<{
        data: EmailCampaignRecipientCount;
      }>,
    ) => {
      state.recipientCount = action.payload.data;
    },
    resetRecipientCount: (state) => {
      state.recipientCount = null;
    },
    setLoadingFetchRecipientCount: (state, action: PayloadAction<boolean>) => {
      state.loadingDelete = action.payload;
    },
    setErrorFetchRecipientCount: (
      state,
      action: PayloadAction<string | null>,
    ) => {
      state.errorDelete = action.payload;
    },
  },
});

export const {
  setCreatedEmailCampaign,
  setLoadingCreate,
  setErrorCreate,
  setUpdatedEmailCampaign,
  setLoadingUpdate,
  setErrorUpdate,
  setFetchedEmailCampaign,
  setLoadingFetch,
  setErrorFetch,
  setDeletedEmailCampaignId,
  setLoadingDelete,
  setErrorDelete,
  setDuplicatedEmailCampaignId,
  setLoadingDuplicate,
  setErrorDuplicate,
  setRecipientCount,
  resetRecipientCount,
  setLoadingFetchRecipientCount,
  setErrorFetchRecipientCount,
} = emailCampaignSlice.actions;

export const createEmailCampaignAction =
  (requestData: CreateEmailCampaignRequest, callback?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setErrorCreate(null));
    try {
      const response = await createEmailCampaign(requestData);
      dispatch(setCreatedEmailCampaign(response));
      if (callback) {
        callback();
      }
    } catch (error) {
      dispatch(
        setErrorCreate(
          error instanceof Error
            ? error.message
            : "Failed to create email campaign.",
        ),
      );
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const updateEmailCampaignAction =
  (
    id: number,
    requestData: UpdateEmailCampaignRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setErrorUpdate(null));
    try {
      const response = await updateEmailCampaign(id, requestData);
      dispatch(setUpdatedEmailCampaign(response));
      if (callback) {
        callback();
      }
    } catch (error) {
      dispatch(
        setErrorUpdate(
          error instanceof Error
            ? error.message
            : "Failed to update email campaign.",
        ),
      );
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const fetchEmailCampaignAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetch(true));
    try {
      const response = await fetchEmailCampaign(id);
      dispatch(setFetchedEmailCampaign(response));
    } catch (error) {
      dispatch(
        setErrorFetch(
          error instanceof Error
            ? error.message
            : "Failed to fetch the email campaign.",
        ),
      );
    } finally {
      dispatch(setLoadingFetch(false));
    }
  };

export const deleteEmailCampaignAction =
  (id: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    dispatch(setErrorDelete(null));
    try {
      await deleteEmailCampaign(id);
      dispatch(setDeletedEmailCampaignId(id));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete email campaign.";
      dispatch(setErrorDelete(message));
      throw error;
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export const duplicateEmailCampaignAction =
  (id: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDuplicate(true));
    dispatch(setErrorDuplicate(null));
    try {
      await duplicateEmailCampaign(id);
      dispatch(setDuplicatedEmailCampaignId(id));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to duplicate email campaign.";
      dispatch(setErrorDuplicate(message));
      throw error;
    } finally {
      dispatch(setLoadingDuplicate(false));
    }
  };

export const fetchEmailCampaignRecipientCountAction =
  (eventSessionId: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetchRecipientCount(true));
    try {
      const response = await fetchEmailCampaignRecipientCount({
        eventSessionId,
      });
      dispatch(setRecipientCount(response));
    } catch (error) {
      dispatch(
        setErrorFetchRecipientCount(
          error instanceof Error
            ? error.message
            : "Failed to fetch the email campaign recipient count.",
        ),
      );
    } finally {
      dispatch(setLoadingFetchRecipientCount(false));
    }
  };

export default emailCampaignSlice.reducer;
