import { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import { AppDispatch } from "@/app/store";
import { RootState } from "@/app/rootReducer";
import {
  fetchCampaignDetailsMetadataAction,
  fetchAggregatedProspectsAction,
  createCampaignAction,
} from "@/modules/stochastic/features/CampaignManagement/slices/createCampaignSlice";
import { FetchAggregatedProspectsRequest } from "@/api/fetchAggregatedProspects/types";
import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import { CreateCampaignRequest } from "@/modules/stochastic/features/CampaignManagement/api/createCampaign/types";
import { useNotification } from "@/context/NotificationContext";

import { fetchCampaignProducts } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/fetchCampaignProductsApi";

/**
 * Local ZipCode interface for this UI
 */
interface ZipCode {
  code: string;
  avgSale: number;
  availableProspects: number;
  selectedProspects: string;
  filteredProspects: number;
}

/**
 * FilterCriteria used in the form
 */
interface FilterCriteria {
  audience: string;
  prospectAge: { min: string; max: string };
  estimatedIncome: string;
  homeAge: string;
  excludeClubMembers: boolean;
  excludeLTV: boolean;
  excludeInstallCustomers: boolean;
  addressType: string;
}

/**
 * The entire FormData structure
 */
interface FormData {
  campaignName: string;
  campaignProduct: CampaignProduct;
  description: string;
  phoneNumber: string;
  startDate: string;
  endDate: string;
  mailingFrequency: string;
  selectedMailingWeeks: number[];
  locations: number[];
  filterCriteria: FilterCriteria;
  zipCodes: ZipCode[];
  tags: string;
}

export function useCreateEditCampaign() {
  const dispatch = useDispatch<AppDispatch>();
  const navigate = useNavigate();
  const { showNotification } = useNotification();

  const {
    campaignDetailsMetadata,
    loadingCampaignDetailsMetadata,
    errorCampaignDetailsMetadata,
    aggregatedProspects,
    loadingAggregatedProspects,
    errorAggregatedProspects,
    loadingCreate,
  } = useSelector((state: RootState) => state.createCampaign);

  const [campaignProducts, setCampaignProducts] = useState<CampaignProduct[]>(
    [],
  );

  const [loadingCampaignProducts, setLoadingCampaignProducts] =
    useState<boolean>(false);
  const [errorCampaignProducts, setErrorCampaignProducts] = useState<
    string | null
  >(null);

  const [formData, setFormData] = useState<FormData>({
    campaignName: "",
    campaignProduct: {
      id: 0,
      name: "",
      type: "",
      description: "",
      category: "",
    },
    description: "",
    phoneNumber: "",
    startDate: "",
    endDate: "",
    mailingFrequency: "",
    selectedMailingWeeks: [],
    locations: [],
    filterCriteria: {
      audience: "include_prospects_only",
      addressType: "include_residential_only",
      prospectAge: { min: "40", max: "90" },
      estimatedIncome: "",
      homeAge: "5",
      excludeClubMembers: false,
      excludeLTV: false,
      excludeInstallCustomers: false,
    },
    zipCodes: [],
    tags: "",
  });

  const [isFiltering, setIsFiltering] = useState(false);
  const [filtersApplied, setFiltersApplied] = useState(false);
  const [filtersDirty, setFiltersDirty] = useState(false);
  const [dateError, setDateError] = useState<string | null>(null);

  useEffect(() => {
    dispatch(fetchCampaignDetailsMetadataAction());

    // Fetch campaign products
    const fetchProducts = async () => {
      setLoadingCampaignProducts(true);
      try {
        const response = await fetchCampaignProducts();
        setCampaignProducts(response.data.campaignProducts);
      } catch (error) {
        console.error("Error fetching campaign products:", error);
        setErrorCampaignProducts("Failed to load campaign products");
      } finally {
        setLoadingCampaignProducts(false);
      }
    };

    fetchProducts();
  }, [dispatch]);

  useEffect(() => {
    const updatedZipCodes: ZipCode[] = aggregatedProspects.map((ap) => {
      return {
        code: ap.postalCode,
        avgSale: ap.avgSales,
        availableProspects: ap.households,
        selectedProspects: "",
        filteredProspects: ap.households,
      };
    });

    setFormData((prev) => ({
      ...prev,
      zipCodes: updatedZipCodes,
    }));
  }, [aggregatedProspects]);

  /**
   * Whenever the audience becomes "include_prospects_only",
   * clear out the existing customer restriction criteria.
   */
  useEffect(() => {
    if (formData.filterCriteria.audience === "include_prospects_only") {
      setFormData((prev) => ({
        ...prev,
        filterCriteria: {
          ...prev.filterCriteria,
          excludeClubMembers: false,
          excludeLTV: false,
          excludeInstallCustomers: false,
        },
      }));
    }
  }, [formData.filterCriteria.audience]);

  /**
   * Show/hide the "Existing Customer Restriction Criteria"
   * based on whether the audience is "include_prospects_only".
   */
  const showExclusionFilters =
    formData.filterCriteria.audience &&
    formData.filterCriteria.audience !== "include_prospects_only";

  const handleInputChange = (field: keyof FormData, value: string) => {
    // If user updates mailingFrequency, ensure selectedMailingWeeks doesn't exceed that new value
    if (field === "mailingFrequency") {
      const newDuration = parseInt(value);
      setFormData((prev) => ({
        ...prev,
        [field]: value,
        selectedMailingWeeks: prev.selectedMailingWeeks.filter(
          (week) => week < newDuration,
        ),
      }));
    } else if (field === "campaignProduct") {
      try {
        const productObj = JSON.parse(value);
        setFormData((prev) => ({
          ...prev,
          [field]: productObj,
        }));
      } catch (e) {
        console.error("Error parsing campaign product:", e);
      }
    } else if (field === "locations") {
      const locationArray = value
        .split(",")
        .map((id) => parseInt(id.trim()))
        .filter((id) => !isNaN(id));
      setFormData((prev) => ({
        ...prev,
        [field]: locationArray,
      }));
    } else {
      setFormData((prev) => ({
        ...prev,
        [field]: value,
      }));
    }
  };

  const handleFilterChange = (
    field: string,
    value: string | boolean | { min: string; max: string },
  ) => {
    setFormData((prev) => ({
      ...prev,
      filterCriteria: {
        ...prev.filterCriteria,
        [field]: value,
      },
    }));
    setFiltersDirty(true);
  };

  const applyFilters = async () => {
    setIsFiltering(true);
    setFiltersDirty(false);

    const requestParams: FetchAggregatedProspectsRequest = {
      customerInclusionRule: formData.filterCriteria.audience,
      addressTypeRule: formData.filterCriteria.addressType,
      prospectMinAgeRule:
        parseInt(formData.filterCriteria.prospectAge.min) || 0,
      prospectMaxAgeRule:
        parseInt(formData.filterCriteria.prospectAge.max) || 999,
      minEstimatedIncomeRule:
        formData.filterCriteria.estimatedIncome || undefined,
      minHomeAgeRule: parseInt(formData.filterCriteria.homeAge) || undefined,

      clubMembersRule: formData.filterCriteria.excludeClubMembers
        ? "exclude_club_members"
        : "",
      lifetimeValueRule: formData.filterCriteria.excludeLTV ? "5000" : "",
      installationsRule: formData.filterCriteria.excludeInstallCustomers
        ? "exclude_customer_installations"
        : "",
      tagsRule: formData.tags,
      locations: formData.locations,
    };

    try {
      await dispatch(fetchAggregatedProspectsAction(requestParams));
      setFiltersApplied(true);
    } catch (error) {
      console.error("Error applying filters:", error);
    } finally {
      setIsFiltering(false);
    }
  };

  const handleZipCodeChange = (index: number, value: string) => {
    const newZipCodes = [...formData.zipCodes];
    newZipCodes[index] = {
      ...newZipCodes[index],
      selectedProspects: value,
    };
    setFormData((prev) => ({
      ...prev,
      zipCodes: newZipCodes,
    }));
  };

  const toggleMailingWeek = (weekIndex: number) => {
    setFormData((prev) => {
      const currentWeeks = prev.selectedMailingWeeks;
      let newWeeks;

      if (currentWeeks.includes(weekIndex)) {
        // If removing an already selected week, allow removal only if more than 1 selected
        if (currentWeeks.length > 1) {
          newWeeks = currentWeeks.filter((w) => w !== weekIndex);
        } else {
          newWeeks = currentWeeks;
        }
      } else {
        newWeeks = [...currentWeeks, weekIndex].sort((a, b) => a - b);
      }

      return {
        ...prev,
        selectedMailingWeeks: newWeeks,
      };
    });
  };

  const getTotalProspects = () => {
    return formData.zipCodes.reduce((sum, zip) => {
      const selected = parseInt(zip.selectedProspects) || 0;
      return sum + selected;
    }, 0);
  };

  const getProspectsPerMailing = () => {
    const total = getTotalProspects();
    return formData.selectedMailingWeeks.length > 0
      ? Math.ceil(total / formData.selectedMailingWeeks.length)
      : total;
  };

  const hasAppliedFilters = () => {
    return formData.zipCodes.some((zip) => parseInt(zip.selectedProspects) > 0);
  };

  const handleCreateCampaign = async () => {
    try {
      // Build the request
      const requestData: CreateCampaignRequest = {
        campaignName: formData.campaignName,
        campaignProduct: formData.campaignProduct,
        description: formData.description,
        phoneNumber: formData.phoneNumber,
        startDate: formData.startDate,
        endDate: formData.endDate,
        mailingFrequency: parseInt(formData.mailingFrequency) || 0,
        selectedMailingWeeks: formData.selectedMailingWeeks,
        locations: formData.locations,
        filterCriteria: formData.filterCriteria,
        zipCodes: formData.zipCodes,
        tags: formData.tags,
      };

      dispatch(
        createCampaignAction(requestData, () => {
          showNotification(
            "Successfully created campaign!",
            "Your campaign has been queued for creation",
            "success",
          );
          // Navigate away
          navigate("/stochastic/campaigns");
        }),
      );
    } catch (err) {
      console.error("Failed to create campaign:", err);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (formData.startDate && formData.endDate) {
      const start = new Date(formData.startDate);
      const end = new Date(formData.endDate);
      if (start > end) {
        setDateError("Start date cannot be later than end date.");
        return;
      }
    }
    setDateError(null);

    handleCreateCampaign();
  };

  return {
    formData,
    setFormData,
    campaignDetailsMetadata,
    loadingCampaignDetailsMetadata,
    loadingCreate,
    errorCampaignDetailsMetadata,
    aggregatedProspects,
    loadingAggregatedProspects,
    errorAggregatedProspects,
    showExclusionFilters,
    isFiltering,
    filtersDirty,
    setFiltersDirty,
    filtersApplied,
    dateError,
    handleInputChange,
    handleFilterChange,
    handleZipCodeChange,
    applyFilters,
    handleSubmit,
    toggleMailingWeek,
    getTotalProspects,
    getProspectsPerMailing,
    hasAppliedFilters,
    campaignProducts,
    loadingCampaignProducts,
    errorCampaignProducts,
  };
}
