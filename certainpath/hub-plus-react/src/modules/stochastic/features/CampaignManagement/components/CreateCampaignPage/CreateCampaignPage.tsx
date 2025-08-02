"use client";

import React, { useEffect, useState } from "react";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { Mail, Info } from "lucide-react";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import { TooltipProvider } from "@/components/ui/tooltip";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { useCreateEditCampaign } from "@/modules/stochastic/features/CampaignManagement/hooks/useCreateEditCampaign";
import "react-phone-number-input/style.css";
import PhoneInput from "react-phone-number-input/input";
import { useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import { useSubscription } from "@apollo/client";
import { ON_COMPANY_DATA_IMPORT_JOB_IN_PROGRESS_COUNT_SUBSCRIPTION } from "@/modules/stochastic/features/CampaignManagement/graphql/subscriptions/onCompanyDataImportJobInProgressCount";
import TagManager from "@/components/TagManager/TagManager";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { EntityMultiSelect } from "@/components/EntityMultiSelect/EntityMultiSelect";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  CampaignFormData,
  CampaignFormSchema,
} from "../../hooks/CampaignFormSchema";
import { useForm } from "react-hook-form";
import { fetchLocations } from "@/modules/stochastic/features/LocationsList/api/fetchLocations/fetchLocationsApi";

function CreateCampaignPage() {
  const {
    formData,
    loadingCampaignDetailsMetadata,
    loadingCreate,
    errorCampaignDetailsMetadata,
    loadingAggregatedProspects,
    errorAggregatedProspects,
    showExclusionFilters,
    isFiltering,
    filtersDirty,
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
    campaignDetailsMetadata,
    campaignProducts,
    loadingCampaignProducts,
    setFiltersDirty,
  } = useCreateEditCampaign();

  const productsArray =
    campaignProducts && typeof campaignProducts === "object"
      ? Object.values(campaignProducts)
      : [];

  const userAppSettings = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );
  const companyId = userAppSettings?.companyId ?? 0;

  const { data: importData } = useSubscription(
    ON_COMPANY_DATA_IMPORT_JOB_IN_PROGRESS_COUNT_SUBSCRIPTION,
    {
      variables: { companyId },
      skip: !companyId,
    },
  );
  const inProgressCount =
    importData?.company_data_import_job_aggregate?.aggregate?.count ?? 0;
  const isImportInProgress = inProgressCount > 0;

  const mailingFrequencies = campaignDetailsMetadata?.mailingFrequencies || [];
  const campaignTargets = campaignDetailsMetadata?.campaignTargets || [];
  const customerRestrictionCriteria =
    campaignDetailsMetadata?.customerRestrictionCriteria || [];
  const estimatedIncomeOptions =
    campaignDetailsMetadata?.estimatedIncomeOptions || [];
  const addressTypeOptions = campaignDetailsMetadata?.addressTypes || [];

  // Hide the entire "Select your Demographic Targets" block if audience === "include_active_customers_only".
  const showDemographicTargets =
    formData.filterCriteria.audience !== "include_active_customers_only";

  // Hide the TagManager block if audience === "include_active_customers_only".
  const showTagManager =
    formData.filterCriteria.audience !== "include_active_customers_only";

  const [customTags, setCustomTags] = useState<string[]>([]);
  const handleCustomTagsChange = (tags: string[]) => {
    setCustomTags(tags);
    handleInputChange("tags", tags.join(","));
  };

  const campaignFormDefaultValues: CampaignFormData = {
    locations: [],
  };

  const form = useForm<CampaignFormData>({
    resolver: zodResolver(CampaignFormSchema),
    defaultValues: campaignFormDefaultValues,
    mode: "onChange",
  });

  const { control } = form;
  const locations = form.watch("locations") || [];

  const [locationsChanged, setLocationsChanged] = useState(false);

  useEffect(() => {
    if (locations.length) {
      setLocationsChanged(true);
    }
  }, [locations]);

  useEffect(() => {
    if (!locationsChanged) return;

    const locationIds = locations.map((loc) => loc.id).join(",");
    handleInputChange("locations", locationIds);
    setFiltersDirty(true);
  }, [locations, locationsChanged]);

  return (
    <MainPageWrapper title="Create New Campaign">
      <TooltipProvider>
        <div>
          {isImportInProgress && (
            <div className="mb-4 border-l-4 border-yellow-400 bg-yellow-50 p-4">
              <p className="text-sm text-yellow-800">
                Campaign creation is disabled while company data is still being
                imported.
              </p>
            </div>
          )}
          {errorCampaignDetailsMetadata && (
            <div className="mb-4 text-red-500">
              Error: {errorCampaignDetailsMetadata}
            </div>
          )}
          {errorAggregatedProspects && (
            <div className="mb-4 text-red-500">
              Error fetching prospects: {errorAggregatedProspects}
            </div>
          )}

          <form className="space-y-8" onSubmit={handleSubmit}>
            <fieldset disabled={isImportInProgress}>
              <Card>
                <CardHeader></CardHeader>
                <CardContent className="space-y-6">
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold">Campaign Details</h3>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-4">
                        <Label>Product</Label>
                        <Select
                          disabled={loadingCampaignProducts}
                          onValueChange={(value) => {
                            const selectedProduct = campaignProducts.find(
                              (product) => String(product.id) === value,
                            );
                            if (selectedProduct) {
                              handleInputChange("campaignProduct", value);
                            }
                          }}
                          required={true}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select a product for billing" />
                          </SelectTrigger>
                          <SelectContent className="bg-white">
                            {productsArray.map((product) => (
                              <SelectItem
                                key={product.id}
                                value={product.id.toString()}
                              >
                                {product.name}
                              </SelectItem>
                            ))}
                            {loadingCampaignProducts && (
                              <SelectItem disabled value="loading">
                                Loading products...
                              </SelectItem>
                            )}
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label>Campaign Name</Label>
                        <Input
                          onChange={(e) =>
                            handleInputChange("campaignName", e.target.value)
                          }
                          placeholder="Enter campaign name"
                          value={formData.campaignName}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>Phone Number</Label>
                        <PhoneInput
                          className="w-full border rounded px-2 py-2"
                          country="US"
                          onChange={(val) =>
                            handleInputChange("phoneNumber", val ?? "")
                          }
                          placeholder="Enter phone number"
                          value={formData.phoneNumber}
                        />
                      </div>
                    </div>
                    <div className="space-y-2">
                      <Label>Description</Label>
                      <Input
                        onChange={(e) =>
                          handleInputChange("description", e.target.value)
                        }
                        placeholder="Short campaign description"
                        value={formData.description}
                      />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label>Start Date</Label>
                        <Input
                          onChange={(e) =>
                            handleInputChange("startDate", e.target.value)
                          }
                          type="date"
                          value={formData.startDate}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>End Date</Label>
                        <Input
                          onChange={(e) =>
                            handleInputChange("endDate", e.target.value)
                          }
                          type="date"
                          value={formData.endDate}
                        />
                        {dateError && (
                          <p className="text-red-600 text-sm mt-1">
                            {dateError}
                          </p>
                        )}
                      </div>
                    </div>
                    <div className="space-y-2">
                      <Form {...form}>
                        <form
                          className="space-y-8 pb-2 bg-white"
                          onSubmit={() => {}}
                        >
                          <FormField
                            control={control}
                            name="locations"
                            render={({ field }) => (
                              <FormItem>
                                <FormLabel>Locations</FormLabel>
                                <FormControl>
                                  <EntityMultiSelect
                                    entityNamePlural="Locations"
                                    entityNameSingular="Location"
                                    fetchEntities={async ({
                                      searchTerm,
                                      page,
                                      pageSize,
                                    }) => {
                                      const response = await fetchLocations({
                                        searchTerm,
                                        page,
                                        perPage: pageSize,
                                        sortBy: "name",
                                        sortOrder: "ASC",
                                        isActive: 1,
                                      });
                                      const { data: locationsData } = response;
                                      const totalCount =
                                        response.meta?.totalCount ??
                                        locationsData.length;
                                      return {
                                        data: locationsData.map((c) => ({
                                          id: c.id,
                                          name: c.name,
                                        })),
                                        totalCount,
                                      };
                                    }}
                                    isFullWidth={true}
                                    onChange={field.onChange}
                                    value={field.value || []}
                                  />
                                </FormControl>
                                <FormMessage />
                              </FormItem>
                            )}
                          />
                        </form>
                      </Form>
                    </div>
                  </div>

                  <Card>
                    <CardHeader>
                      <CardTitle>Mailing Schedule</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-6">
                        <div className="space-y-4">
                          <div className="flex items-center gap-2">
                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                              {/*<Label>Mailing Frequency</Label>*/}

                              <select
                                className="block w-full rounded-md border border-input bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                                onChange={(e) =>
                                  handleInputChange(
                                    "mailingFrequency",
                                    e.target.value,
                                  )
                                }
                                value={formData.mailingFrequency}
                              >
                                {/* Optionally show a placeholder option */}
                                <option value="">
                                  Select campaign duration
                                </option>

                                {mailingFrequencies.map((mf) => (
                                  <option key={mf.value} value={mf.value}>
                                    {mf.name}
                                  </option>
                                ))}
                              </select>
                            </div>
                          </div>
                          <p className="text-sm text-gray-500">
                            Number of mailings sent within each frequency cycle
                          </p>
                        </div>

                        {parseInt(formData.mailingFrequency) > 0 && (
                          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                            {Array.from(
                              {
                                length:
                                  parseInt(formData.mailingFrequency) || 0,
                              },
                              (_, index) => {
                                const weekNumber = index + 1;

                                return (
                                  <div
                                    className={`
                                  p-4 rounded-lg border cursor-pointer transition-all
                                  ${
                                    formData.selectedMailingWeeks.includes(
                                      weekNumber,
                                    )
                                      ? "bg-blue-100 border-blue-500"
                                      : "hover:bg-gray-50"
                                  }
                                `}
                                    key={index}
                                    onClick={() =>
                                      toggleMailingWeek(weekNumber)
                                    }
                                  >
                                    <div className="flex flex-col items-center space-y-2">
                                      <Mail
                                        className={`w-6 h-6 ${
                                          formData.selectedMailingWeeks.includes(
                                            weekNumber,
                                          )
                                            ? "text-blue-600"
                                            : "text-gray-400"
                                        }`}
                                      />
                                      <div className="text-sm font-medium">
                                        Week {weekNumber}
                                      </div>
                                      {formData.selectedMailingWeeks.includes(
                                        weekNumber,
                                      ) &&
                                        hasAppliedFilters() && (
                                          <div className="text-xs text-blue-600 font-medium">
                                            {getProspectsPerMailing().toLocaleString()}{" "}
                                            mailings
                                          </div>
                                        )}
                                      {formData.selectedMailingWeeks.includes(
                                        weekNumber,
                                      ) &&
                                        !hasAppliedFilters() && (
                                          <div className="text-xs text-gray-500">
                                            Apply filters first
                                          </div>
                                        )}
                                    </div>
                                  </div>
                                );
                              },
                            )}
                          </div>
                        )}

                        <div className="mt-6">
                          {hasAppliedFilters() ? (
                            <div className="text-sm text-gray-500">
                              Total Prospects:{" "}
                              {getTotalProspects().toLocaleString()}
                            </div>
                          ) : (
                            <div className="text-sm text-gray-500">
                              Apply filters to see total prospects
                            </div>
                          )}
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold">
                      Select your Campaign Target
                    </h3>
                    <RadioGroup
                      className="space-y-2"
                      onValueChange={(value) =>
                        handleFilterChange("audience", value)
                      }
                      value={formData.filterCriteria.audience}
                    >
                      {campaignTargets.map((target) => (
                        <div
                          className="flex items-center space-x-2"
                          key={target.value}
                        >
                          <RadioGroupItem
                            id={target.value}
                            value={target.value}
                          />
                          <Label htmlFor={target.value}>{target.name}</Label>
                        </div>
                      ))}
                    </RadioGroup>
                  </div>

                  {/* Address Type Selection */}
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold">Address Type</h3>
                    <RadioGroup
                      className="space-y-2"
                      onValueChange={(value) =>
                        handleFilterChange("addressType", value)
                      }
                      value={formData.filterCriteria.addressType}
                    >
                      {addressTypeOptions.map((addressType) => (
                        <div
                          className="flex items-center space-x-2"
                          key={addressType.value}
                        >
                          <RadioGroupItem
                            id={addressType.value}
                            value={addressType.value}
                          />
                          <Label htmlFor={addressType.value}>{addressType.name}</Label>
                        </div>
                      ))}
                    </RadioGroup>
                  </div>

                  {/* Only show TagManager if the audience is not active_customers_only */}
                  {showTagManager && (
                    <div className="space-y-4">
                      <h3 className="text-lg font-semibold">
                        Select your Tags
                      </h3>
                      <TagManager
                        createNew={false}
                        existingTags={customTags}
                        maxTags={5} // Optional: limit to 5 tags
                        onTagsChange={handleCustomTagsChange}
                        required={false}
                      />
                    </div>
                  )}

                  {showDemographicTargets && (
                    <div className="space-y-4">
                      <h3 className="text-lg font-semibold">
                        Select your Demographic Targets
                      </h3>

                      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div className="space-y-2">
                          <Label>Prospect Age Range</Label>
                          <div className="flex gap-2">
                            <Input
                              onChange={(e) =>
                                handleFilterChange("prospectAge", {
                                  ...formData.filterCriteria.prospectAge,
                                  min: e.target.value,
                                })
                              }
                              placeholder="Min"
                              type="number"
                              value={formData.filterCriteria.prospectAge.min}
                            />
                            <Input
                              onChange={(e) =>
                                handleFilterChange("prospectAge", {
                                  ...formData.filterCriteria.prospectAge,
                                  max: e.target.value,
                                })
                              }
                              placeholder="Max"
                              type="number"
                              value={formData.filterCriteria.prospectAge.max}
                            />
                          </div>
                        </div>

                        <div className="space-y-2">
                          <Label>Minimum Estimated Income</Label>
                          <Select
                            onValueChange={(value) =>
                              handleFilterChange("estimatedIncome", value)
                            }
                            value={formData.filterCriteria.estimatedIncome}
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Select min estimated income" />
                            </SelectTrigger>
                            <SelectContent className="bg-white">
                              {estimatedIncomeOptions.map((nw) => (
                                <SelectItem
                                  key={nw.value}
                                  value={nw.value.toString()}
                                >
                                  {nw.name}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>

                        <div className="space-y-2">
                          <Label>Minimum Home Age</Label>
                          <Input
                            onChange={(e) =>
                              handleFilterChange("homeAge", e.target.value)
                            }
                            placeholder="Enter years"
                            type="number"
                            value={formData.filterCriteria.homeAge}
                          />
                        </div>
                      </div>
                    </div>
                  )}

                  {showExclusionFilters && (
                    <div className="space-y-4">
                      <h3 className="text-lg font-semibold">
                        Existing Customer Restriction Criteria
                      </h3>
                      <div className="grid grid-cols-2 gap-4">
                        {customerRestrictionCriteria.map((c) => {
                          // NEW: Check for LTV or Installations for tooltips
                          let tooltipContent: string | null = null;
                          if (c.value === "5000") {
                            tooltipContent =
                              "Excludes customers whose total combined purchases across all invoices exceed $5,000.";
                          } else if (
                            c.value === "exclude_customer_installations"
                          ) {
                            tooltipContent =
                              "Excludes customers who have any single invoice of $2,500 or more. We assume an invoice of $2,500 or more indicates installation or replacement.";
                          }

                          return (
                            <div
                              className="flex items-center space-x-2"
                              key={c.value}
                            >
                              <Checkbox
                                checked={
                                  (c.value === "exclude_club_members" &&
                                    formData.filterCriteria
                                      .excludeClubMembers) ||
                                  (c.value === "5000" &&
                                    formData.filterCriteria.excludeLTV) ||
                                  (c.value ===
                                    "exclude_customer_installations" &&
                                    formData.filterCriteria
                                      .excludeInstallCustomers)
                                }
                                id={c.value}
                                onCheckedChange={(checked) => {
                                  if (c.value === "exclude_club_members") {
                                    handleFilterChange(
                                      "excludeClubMembers",
                                      checked,
                                    );
                                  } else if (c.value === "5000") {
                                    handleFilterChange("excludeLTV", checked);
                                  } else if (
                                    c.value === "exclude_customer_installations"
                                  ) {
                                    handleFilterChange(
                                      "excludeInstallCustomers",
                                      checked,
                                    );
                                  }
                                }}
                              />
                              <Label htmlFor={c.value}>{c.name}</Label>

                              {/* NEW: Tooltip for LTV/Installations */}
                              {tooltipContent && (
                                <Tooltip>
                                  <TooltipTrigger asChild>
                                    <span>
                                      <Info className="w-4 h-4 text-gray-500 cursor-pointer" />
                                    </span>
                                  </TooltipTrigger>
                                  <TooltipContent>
                                    <p className="text-sm">{tooltipContent}</p>
                                  </TooltipContent>
                                </Tooltip>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  )}

                  <div className="flex justify-end items-center gap-4">
                    {filtersDirty && (
                      <span className="text-yellow-600 text-sm">
                        Filters have changed. Please reapply filters to update
                        prospect counts.
                      </span>
                    )}
                    <Button
                      disabled={isFiltering || loadingCampaignDetailsMetadata}
                      onClick={applyFilters}
                      type="button"
                      variant={filtersDirty ? "default" : "outline"}
                    >
                      {isFiltering || loadingAggregatedProspects
                        ? "Applying Filters..."
                        : "Apply Filters"}
                    </Button>
                  </div>

                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold">
                      Select Postal Codes
                    </h3>

                    {/* Mobile (stacked card) layout */}
                    <div className="block md:hidden space-y-4">
                      {formData.zipCodes.map((zipCode, index) => (
                        <div
                          className="rounded-md border p-4 shadow-sm transition-colors hover:bg-muted/50"
                          key={zipCode.code}
                        >
                          <div className="mb-2 text-sm font-medium text-gray-700">
                            Postal Code:{" "}
                            <span className="font-normal">{zipCode.code}</span>
                          </div>
                          <div className="mb-2 text-sm font-medium text-gray-700">
                            Avg Sales:{" "}
                            <span className="font-normal">
                              ${zipCode.avgSale.toLocaleString()}
                            </span>
                          </div>
                          <div className="mb-2 text-sm font-medium text-gray-700">
                            Households:{" "}
                            <span className="font-normal">
                              {isFiltering || loadingAggregatedProspects ? (
                                <span className="text-gray-400">
                                  Calculating...
                                </span>
                              ) : (
                                zipCode.filteredProspects.toLocaleString()
                              )}
                            </span>
                          </div>
                          <div className="text-sm font-medium text-gray-700">
                            Select Prospects:
                          </div>
                          <div className="mt-1 flex items-center justify-start gap-2">
                            <Input
                              className="w-24"
                              disabled={!filtersApplied || filtersDirty}
                              max={zipCode.filteredProspects}
                              min={0}
                              onChange={(e) =>
                                handleZipCodeChange(index, e.target.value)
                              }
                              type="number"
                              value={zipCode.selectedProspects}
                            />
                            <div className="flex gap-1">
                              <Button
                                disabled={!filtersApplied || filtersDirty}
                                onClick={() => handleZipCodeChange(index, "")}
                                size="sm"
                                type="button"
                                variant="outline"
                              >
                                Clear
                              </Button>
                              <Button
                                disabled={!filtersApplied || filtersDirty}
                                onClick={() =>
                                  handleZipCodeChange(
                                    index,
                                    zipCode.filteredProspects.toString(),
                                  )
                                }
                                size="sm"
                                type="button"
                                variant="outline"
                              >
                                Max
                              </Button>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>

                    {/* Desktop (table) layout */}
                    <div className="hidden md:block relative w-full overflow-auto rounded-md border shadow-sm">
                      <table className="w-full caption-bottom text-sm">
                        <thead>
                          <tr>
                            <th className="p-4 text-left align-middle">
                              Postal Code
                            </th>
                            <th className="p-4 text-left align-middle">
                              Avg Sales
                            </th>
                            <th className="p-4 text-left align-middle">
                              Households
                            </th>
                            <th className="p-4 text-right align-middle">
                              Select Prospects
                            </th>
                          </tr>
                        </thead>
                        <tbody className="[&_tr:last-child]:border-0">
                          {formData.zipCodes.map((zipCode, index) => (
                            <tr
                              className="border-b transition-colors hover:bg-muted/50"
                              key={zipCode.code}
                            >
                              <td className="p-4 align-middle">
                                {zipCode.code}
                              </td>

                              <td className="p-4 align-middle">
                                ${zipCode.avgSale.toLocaleString()}
                              </td>

                              <td className="p-4 align-middle">
                                {isFiltering || loadingAggregatedProspects ? (
                                  <span className="text-gray-400">
                                    Calculating...
                                  </span>
                                ) : (
                                  zipCode.filteredProspects.toLocaleString()
                                )}
                              </td>

                              <td className="p-4 align-middle text-right">
                                <div className="flex items-center justify-end gap-2">
                                  <Input
                                    className="w-24"
                                    disabled={!filtersApplied || filtersDirty}
                                    max={zipCode.filteredProspects}
                                    min={0}
                                    onChange={(e) =>
                                      handleZipCodeChange(index, e.target.value)
                                    }
                                    type="number"
                                    value={zipCode.selectedProspects}
                                  />
                                  <div className="flex gap-1">
                                    <Button
                                      disabled={!filtersApplied || filtersDirty}
                                      onClick={() =>
                                        handleZipCodeChange(index, "")
                                      }
                                      size="sm"
                                      type="button"
                                      variant="outline"
                                    >
                                      Clear
                                    </Button>
                                    <Button
                                      disabled={!filtersApplied || filtersDirty}
                                      onClick={() =>
                                        handleZipCodeChange(
                                          index,
                                          zipCode.filteredProspects.toString(),
                                        )
                                      }
                                      size="sm"
                                      type="button"
                                      variant="outline"
                                    >
                                      Max
                                    </Button>
                                  </div>
                                </div>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <CardFooter className="p-0 flex flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:justify-end sm:gap-4">
                    <Button type="button" variant="outline">
                      Cancel
                    </Button>
                    <Button
                      disabled={
                        !filtersApplied ||
                        filtersDirty ||
                        isFiltering ||
                        !formData.zipCodes.some(
                          (zip) => zip.selectedProspects !== "",
                        )
                      }
                      type="submit"
                    >
                      {loadingCreate ? "Submitting..." : "Create Campaign"}
                    </Button>
                  </CardFooter>
                </CardContent>
              </Card>
            </fieldset>
          </form>
        </div>
      </TooltipProvider>
    </MainPageWrapper>
  );
}

export default CreateCampaignPage;
