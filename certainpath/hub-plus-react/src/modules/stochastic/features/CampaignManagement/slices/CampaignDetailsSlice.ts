import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CampaignDetailsData,
  GetCampaignDetailsRequest,
} from "../api/fetchCampaignDetails/types";
import { getCampaignDetails } from "../api/fetchCampaignDetails/getCampaignDetailsApi";

const defaultCampaignDetails: CampaignDetailsData = {
  id: 0,
  intacctId: "",
  name: "",
  phoneNumber: "",
  description: "",
  startDate: "",
  endDate: "",
  campaignStatus: {
    id: 0,
    name: "",
  },
  campaignProduct: {
    id: 0,
    name: "",
  },
  locations: [],
  mailingSchedule: {
    mailingFrequency: {
      value: 0,
      label: "",
    },
    mailingDropWeeks: [],
  },
  filters: {
    tags: [],
    campaignTarget: {
      name: "",
      value: "",
    },
    demographicTargets: [],
    addressType: {
      name: "",
      value: "",
    },
    customerRestrictionCriteria: [],
  },
  postalCodeLimits: [],
  totalProspects: 0,
  canBePaused: false,
  canBeStopped: false,
  canBeResumed: false,
  showDemographicTargets: false,
  showTagSelector: false,
  showCustomerRestrictionCriteria: false,
};

interface CampaignDetailsState {
  campaignDetails: CampaignDetailsData;
  loading: boolean;
  error: string | null;
}

const initialState: CampaignDetailsState = {
  campaignDetails: defaultCampaignDetails,
  loading: false,
  error: null,
};

const campaignDetailsSlice = createSlice({
  name: "campaignDetails",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setCampaignDetails: (state, action: PayloadAction<CampaignDetailsData>) => {
      state.campaignDetails = action.payload;
    },
    clearCampaignDetails: (state) => {
      state.campaignDetails = defaultCampaignDetails;
      state.error = null;
    },
  },
});

export const {
  setLoading,
  setError,
  setCampaignDetails,
  clearCampaignDetails,
} = campaignDetailsSlice.actions;

export const fetchCampaignDetailsAction =
  (requestData: GetCampaignDetailsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));

    try {
      const response = await getCampaignDetails(requestData.campaignId);
      dispatch(setCampaignDetails(response.data));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch campaign details.",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default campaignDetailsSlice.reducer;
