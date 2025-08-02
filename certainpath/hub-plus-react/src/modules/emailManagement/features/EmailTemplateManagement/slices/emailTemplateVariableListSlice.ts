import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { EmailTemplateVariable } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateVariables/types";
import { fetchEmailTemplateVariables } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateVariables/fetchEmailTemplateVariablesApi";

interface EmailTemplateVariablesState {
  emailTemplateVariables: EmailTemplateVariable[] | null;
  totalCount: number | undefined;
  loading: boolean;
  error: string | null;
}

const initialState: EmailTemplateVariablesState = {
  emailTemplateVariables: null,
  totalCount: undefined,
  loading: false,
  error: null,
};

const emailTemplateSlice = createSlice({
  name: "emailTemplateVariableList",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setEmailTemplateVariablesData: (
      state,
      action: PayloadAction<{
        emailTemplateVariables: EmailTemplateVariable[];
        totalCount: number | undefined;
      }>,
    ) => {
      state.emailTemplateVariables = action.payload.emailTemplateVariables;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setEmailTemplateVariablesData } =
  emailTemplateSlice.actions;

export const fetchEmailTemplateVariablesAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response = await fetchEmailTemplateVariables({});
      dispatch(
        setEmailTemplateVariablesData({
          emailTemplateVariables: response.data,
          totalCount: response.meta?.totalCount,
        }),
      );
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch email template variables",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default emailTemplateSlice.reducer;
