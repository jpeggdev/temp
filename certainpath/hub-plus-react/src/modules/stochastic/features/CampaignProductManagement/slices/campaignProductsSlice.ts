import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  FetchCampaignProductsResponse,
  CampaignProduct,
} from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { fetchCampaignProducts } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/fetchCampaignProductsApi";
import { createCampaignProduct } from "@/api/createCampaignProduct/createCampaignProductApi";
import { updateCampaignProduct } from "@/api/updateCampaignProduct/updateCampaignProductApi";
import { deleteCampaignProduct } from "@/api/deleteCampaignProduct/deleteCampaignProductApi";
import { CreateCampaignProductRequest } from "@/api/createCampaignProduct/types";

interface CampaignProductsState {
  campaignProducts: CampaignProduct[];
  totalCount: number;
  loading: boolean;
  error: string | null;
  currentProduct: CampaignProduct | null;
}

const initialState: CampaignProductsState = {
  campaignProducts: [],
  totalCount: 0,
  loading: false,
  error: null,
  currentProduct: null,
};

const campaignProductsSlice = createSlice({
  name: "campaignProducts",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setCampaignProductsData: (
      state,
      action: PayloadAction<{
        campaignProducts: CampaignProduct[];
        totalCount: number;
      }>,
    ) => {
      state.campaignProducts = action.payload.campaignProducts;
      state.totalCount = action.payload.totalCount;
    },
    addCampaignProduct: (state, action: PayloadAction<CampaignProduct>) => {
      state.campaignProducts.push(action.payload);
      state.totalCount += 1;
    },
    updateCampaignProductInState: (
      state,
      action: PayloadAction<CampaignProduct>,
    ) => {
      const index = state.campaignProducts.findIndex(
        (p) => p.id === action.payload.id,
      );
      if (index !== -1) {
        state.campaignProducts[index] = action.payload;
      }
    },
    removeCampaignProduct: (state, action: PayloadAction<string | number>) => {
      state.campaignProducts = state.campaignProducts.filter(
        (p) => p.id !== action.payload,
      );
      state.totalCount -= 1;
    },
    setCurrentProduct: (
      state,
      action: PayloadAction<CampaignProduct | null>,
    ) => {
      state.currentProduct = action.payload;
    },
  },
});

export const {
  setLoading,
  setError,
  setCampaignProductsData,
  addCampaignProduct,
  updateCampaignProductInState,
  removeCampaignProduct,
  setCurrentProduct,
} = campaignProductsSlice.actions;

export const fetchCampaignProductsAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchCampaignProductsResponse =
        await fetchCampaignProducts();

      const campaignProducts = response.data.campaignProducts || [];
      const totalCount = response.meta?.totalCount || 0;

      dispatch(
        setCampaignProductsData({
          campaignProducts,
          totalCount,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch campaign products"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export const createCampaignProductAction =
  (product: CreateCampaignProductRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const newProduct = await createCampaignProduct(product);
      dispatch(addCampaignProduct(newProduct));
      return newProduct;
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to create campaign product"));
      }
      throw error;
    } finally {
      dispatch(setLoading(false));
    }
  };

export const updateCampaignProductAction =
  (
    id: string | number,
    product: Partial<CreateCampaignProductRequest>,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const updatedProduct = await updateCampaignProduct(id, product);
      dispatch(updateCampaignProductInState(updatedProduct));
      return updatedProduct;
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to update campaign product"));
      }
      throw error;
    } finally {
      dispatch(setLoading(false));
    }
  };

export const deleteCampaignProductAction =
  (id: string | number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      await deleteCampaignProduct(id);
      dispatch(removeCampaignProduct(id));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to delete campaign product"));
      }
      throw error;
    } finally {
      dispatch(setLoading(false));
    }
  };

export default campaignProductsSlice.reducer;
