import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { fetchCompanies } from "../../../../../api/fetchCompanies/fetchCompaniesApi";
import {
  FetchCompaniesRequest,
  FetchCompaniesResponse,
  ApiCompany,
} from "../../../../../api/fetchCompanies/types";
import { createCompany } from "../../../../../api/createCompany/createCompanyApi";
import {
  CreateCompanyRequest,
  CreateCompanyResponse,
} from "../../../../../api/createCompany/types";
import { getEditCompanyDetails } from "../../../../../api/getEditCompanyDetails/getEditCompanyDetailsApi";
import {
  GetEditCompanyDetailsResponse,
  FieldServiceSoftware,
  Trade,
} from "../../../../../api/getEditCompanyDetails/types";
import { editCompany } from "../../../../../api/editCompany/editCompanyApi";
import {
  EditCompanyDTO,
  EditCompanyResponse,
} from "../../../../../api/editCompany/types";
import {
  UpdateFieldServiceSoftwareDTO,
  UpdateFieldServiceSoftwareResponse,
} from "../../../../../api/updateFieldServiceSoftware/types";
import { updateFieldServiceSoftware } from "../../../../../api/updateFieldServiceSoftware/updateFieldServiceSoftwareApi";
import {
  UpdateCompanyTradeDTO,
  UpdateCompanyTradeResponse,
} from "../../../../../api/updateCompanyTrade/types";
import { updateCompanyTrade } from "../../../../../api/updateCompanyTrade/updateCompanyTradeApi";
export interface Company {
  id: number;
  companyName: string;
  uuid: string;
  salesforceId?: string | null;
  intacctId?: string | null;
  marketingEnabled?: boolean;
  companyEmail?: string | null;
  websiteUrl?: string | null;
  fieldServiceSoftwareId?: number | null;
  fieldServiceSoftwareName?: string | null;
  fieldServiceSoftwareList?: FieldServiceSoftware[];
  tradeIds?: number[]; // List of associated trade IDs
  createdAt?: string | null;
}

interface CompaniesState {
  companies: Company[];
  selectedCompany: Company | null;
  tradeList: Trade[]; // Full list of trades
  companyTradeIds: number[]; // Trades associated with the company
  totalCount: number;
  loading: boolean;
  error: string | null;
  updatingTrades: boolean;
  tradeError: string | null;
}

const initialState: CompaniesState = {
  companies: [],
  selectedCompany: null,
  tradeList: [],
  companyTradeIds: [],
  totalCount: 0,
  loading: false,
  error: null,
  updatingTrades: false,
  tradeError: null,
};

const companiesSlice = createSlice({
  name: "companies",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setCompaniesData: (
      state,
      action: PayloadAction<{ companies: Company[]; totalCount: number }>,
    ) => {
      state.companies = action.payload.companies;
      state.totalCount = action.payload.totalCount;
    },
    setSelectedCompany: (state, action: PayloadAction<Company | null>) => {
      state.selectedCompany = action.payload;
    },
    updateCompany: (
      state,
      action: PayloadAction<Partial<Company> & { uuid: string }>,
    ) => {
      // Update selectedCompany if it matches the UUID
      if (
        state.selectedCompany &&
        state.selectedCompany.uuid === action.payload.uuid
      ) {
        state.selectedCompany = { ...state.selectedCompany, ...action.payload };
      }

      // Find and update the company in the companies array
      const index = state.companies.findIndex(
        (company) => company.uuid === action.payload.uuid,
      );
      if (index !== -1) {
        state.companies[index] = {
          ...state.companies[index],
          ...action.payload,
        };
      }
    },
    addCompany: (state, action: PayloadAction<Company>) => {
      state.companies.push(action.payload);
    },
    updateSelectedCompanyFieldServiceSoftware: (
      state,
      action: PayloadAction<{
        fieldServiceSoftwareId: number | null;
        fieldServiceSoftwareName: string | null;
      }>,
    ) => {
      if (state.selectedCompany) {
        state.selectedCompany.fieldServiceSoftwareId =
          action.payload.fieldServiceSoftwareId;
        state.selectedCompany.fieldServiceSoftwareName =
          action.payload.fieldServiceSoftwareName;
      }
    },
    setUpdatingTrades: (state, action: PayloadAction<boolean>) => {
      state.updatingTrades = action.payload;
    },
    setTradeError: (state, action: PayloadAction<string | null>) => {
      state.tradeError = action.payload;
    },
    updateCompanyTrades: (
      state,
      action: PayloadAction<{ tradeIds: number[] }>,
    ) => {
      if (state.selectedCompany) {
        state.selectedCompany.tradeIds = action.payload.tradeIds;
      }
      state.companyTradeIds = action.payload.tradeIds;
    },
    setTradeList: (state, action: PayloadAction<Trade[]>) => {
      state.tradeList = action.payload;
    },
    setCompanyTradeIds: (state, action: PayloadAction<number[]>) => {
      state.companyTradeIds = action.payload;
    },
  },
});

export const {
  setLoading,
  setError,
  setCompaniesData,
  setSelectedCompany,
  updateCompany,
  addCompany,
  updateSelectedCompanyFieldServiceSoftware,
  setUpdatingTrades,
  setTradeError,
  updateCompanyTrades,
  setTradeList,
  setCompanyTradeIds,
} = companiesSlice.actions;

// Thunks

export const fetchCompaniesAction =
  (requestData: FetchCompaniesRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchCompaniesResponse =
        await fetchCompanies(requestData);

      const mappedCompanies: Company[] = response.data.companies.map(
        (apiCompany: ApiCompany): Company => ({
          id: apiCompany.id,
          companyName: apiCompany.companyName,
          uuid: apiCompany.uuid,
          salesforceId: apiCompany.salesforceId || null,
          intacctId: apiCompany.intacctId || null,
          marketingEnabled: apiCompany.marketingEnabled || false,
        }),
      );

      dispatch(
        setCompaniesData({
          companies: mappedCompanies,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error ? error.message : "Failed to fetch companies",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const createCompanyAction =
  (
    requestData: CreateCompanyRequest,
    callback?: (newCompany: Company) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: CreateCompanyResponse = await createCompany(requestData);
      const newCompanyFromApi = response.data;

      const newCompany: Company = {
        id: newCompanyFromApi.id || 0,
        companyName: newCompanyFromApi.companyName || "Unnamed Company",
        uuid: newCompanyFromApi.uuid || "",
        salesforceId: newCompanyFromApi.salesforceId || null,
        intacctId: newCompanyFromApi.intacctId || null,
        companyEmail: newCompanyFromApi.companyEmail || null,
        websiteUrl: newCompanyFromApi.websiteUrl || null,
      };

      dispatch(addCompany(newCompany));

      if (callback) {
        callback(newCompany);
      }
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error ? error.message : "Failed to create company",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const fetchEditCompanyDetailsAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: GetEditCompanyDetailsResponse =
        await getEditCompanyDetails(uuid);

      const companyDetails: Company = {
        id: 0,
        companyName: response.data.companyName,
        uuid: uuid,
        salesforceId: response.data.salesforceId,
        intacctId: response.data.intacctId,
        marketingEnabled: response.data.marketingEnabled,
        companyEmail: response.data.companyEmail,
        websiteUrl: response.data.websiteUrl,
        fieldServiceSoftwareId: response.data.fieldServiceSoftwareId,
        fieldServiceSoftwareName: response.data.fieldServiceSoftwareName,
        fieldServiceSoftwareList: response.data.fieldServiceSoftwareList,
        tradeIds: response.data.companyTradeIds || [],
      };

      dispatch(setSelectedCompany(companyDetails));
      dispatch(setTradeList(response.data.tradeList));
      dispatch(setCompanyTradeIds(response.data.companyTradeIds || []));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch company details",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const editCompanyAction =
  (
    uuid: string,
    editCompanyDTO: EditCompanyDTO,
    callback?: (updatedCompany: Company) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: EditCompanyResponse = await editCompany(
        uuid,
        editCompanyDTO,
      );

      const updatedCompany: Company = {
        id: 0,
        companyName: response.data.companyName,
        uuid: uuid,
        salesforceId: response.data.salesforceId,
        intacctId: response.data.intacctId,
        marketingEnabled: response.data.marketingEnabled,
        companyEmail: response.data.companyEmail,
        websiteUrl: response.data.websiteUrl,
        fieldServiceSoftwareId: response.data.fieldServiceSoftwareId,
        fieldServiceSoftwareName: response.data.fieldServiceSoftwareName,
      };

      dispatch(updateCompany(updatedCompany));
      dispatch(setCompanyTradeIds(updatedCompany.tradeIds || []));

      if (callback) {
        callback(updatedCompany);
      }
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to edit company details",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export const updateFieldServiceSoftwareAction =
  (
    uuid: string,
    updateFieldServiceSoftwareDTO: UpdateFieldServiceSoftwareDTO,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: UpdateFieldServiceSoftwareResponse =
        await updateFieldServiceSoftware(uuid, updateFieldServiceSoftwareDTO);

      dispatch(
        updateSelectedCompanyFieldServiceSoftware({
          fieldServiceSoftwareId: response.data.fieldServiceSoftwareId,
          fieldServiceSoftwareName: response.data.fieldServiceSoftwareName,
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
            : "Failed to update field service software",
        ),
      );
      throw error;
    } finally {
      dispatch(setLoading(false));
    }
  };

export const updateCompanyTradeAction =
  (
    uuid: string,
    updateCompanyTradeDTO: UpdateCompanyTradeDTO,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setUpdatingTrades(true));
    dispatch(setTradeError(null));

    try {
      const response: UpdateCompanyTradeResponse = await updateCompanyTrade(
        uuid,
        updateCompanyTradeDTO,
      );

      // Update the company trades in the selected company
      dispatch(
        updateCompanyTrades({
          tradeIds: response.data.tradeIds,
        }),
      );

      if (callback) {
        callback();
      }
    } catch (error) {
      dispatch(
        setTradeError(
          error instanceof Error ? error.message : "Failed to update trade",
        ),
      );
      throw error;
    } finally {
      dispatch(setUpdatingTrades(false));
    }
  };

export default companiesSlice.reducer;
