"use client";

import React from "react";
import { useNavigate } from "react-router-dom";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Mail, Info, ArrowLeft } from "lucide-react";
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import { TooltipProvider } from "@/components/ui/tooltip";
import { useViewCampaignDetails } from "@/modules/stochastic/features/CampaignManagement/hooks/useViewCampaignDetails";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/ui/button";
import PauseCampaignModal from "@/modules/stochastic/features/CampaignManagement/components/PauseCampaignModal/PauseCampaignModal";
import StopCampaignModal from "@/modules/stochastic/features/CampaignManagement/components/StopCampaignModal/StopCampaignModal";
import ResumeCampaignModal from "@/modules/stochastic/features/CampaignManagement/components/ResumeCampaignModal/ResumeCampaignModal";
import {
  Location,
  Tag,
  DemographicTarget,
  CustomerRestrictionCriterion,
  AddressType,
} from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignDetails/types";

function ViewCampaignDetailsPage() {
  const navigate = useNavigate();
  const {
    loading,
    error,
    campaignDetails,
    showStopModal,
    showPauseModal,
    showResumeModal,
    handleShowStopModal,
    handleCloseStopModal,
    handleShowPauseModal,
    handleClosePauseModal,
    handleShowResumeModal,
    handleCloseResumeModal,
    handleStopModalSuccess,
    handlePauseModalSuccess,
    handleResumeModalSuccess,
  } = useViewCampaignDetails();

  const getDemographicTargetValue = (
    targets: DemographicTarget[] | undefined,
    name: string,
  ): string => {
    const target = targets?.find((t) => t.name === name);
    return target?.value ?? "";
  };

  const formatDate = (dateString: string): string => {
    if (!dateString) return "";
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "";

    return date.toLocaleDateString("en-US", {
      month: "2-digit",
      day: "2-digit",
      year: "numeric",
    });
  };

  const formatPhoneNumber = (phoneNumber: string): string => {
    if (!phoneNumber) return "";

    const cleanNumber = phoneNumber.replace(/\D/g, "");

    if (cleanNumber.length === 10) {
      return `(${cleanNumber.slice(0, 3)}) ${cleanNumber.slice(3, 6)}-${cleanNumber.slice(6)}`;
    }

    if (cleanNumber.length === 11 && cleanNumber.startsWith("1")) {
      return `+1 (${cleanNumber.slice(1, 4)}) ${cleanNumber.slice(4, 7)}-${cleanNumber.slice(7)}`;
    }

    return phoneNumber;
  };

  return (
    <MainPageWrapper error={error} loading={loading} title="Campaign Details">
      {campaignDetails && (
        <TooltipProvider>
          <div className="space-y-8">
            <Card>
              <CardHeader>
                <Button
                  className="w-fit mb-4 px-3 py-2 h-auto text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
                  onClick={() => navigate("/stochastic/campaigns")}
                  variant="ghost"
                >
                  <ArrowLeft className="mr-0.5 h-5 w-10 text-gray-400" />
                  <span className="text-gray-500">Back</span>
                </Button>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label>Product</Label>
                      <Input
                        className="bg-gray-50"
                        readOnly
                        value={
                          campaignDetails.campaignProduct.name ||
                          "No product selected"
                        }
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label>Campaign Name</Label>
                      <Input
                        className="bg-gray-50"
                        readOnly
                        value={campaignDetails.name || "Not specified"}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>Phone Number</Label>
                      <Input
                        className="bg-gray-50"
                        readOnly
                        value={
                          campaignDetails.phoneNumber
                            ? formatPhoneNumber(campaignDetails.phoneNumber)
                            : "Not specified"
                        }
                      />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label>Description</Label>
                    <Input
                      className="bg-gray-50"
                      readOnly
                      value={campaignDetails.description || "Not specified"}
                    />
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label>Start Date</Label>
                      <Input
                        className="bg-gray-50"
                        readOnly
                        value={formatDate(campaignDetails.startDate)}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>End Date</Label>
                      <Input
                        className="bg-gray-50"
                        readOnly
                        value={formatDate(campaignDetails.endDate)}
                      />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <div className="text-sm font-medium text-gray-700">
                      Locations
                    </div>
                    <div className="bg-gray-50 px-3 py-2 rounded-md border">
                      {campaignDetails.locations.length > 0 ? (
                        <div className="flex flex-wrap gap-2">
                          {campaignDetails.locations.map(
                            (location: Location) => (
                              <span
                                className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full"
                                key={location.id}
                              >
                                {location.name}
                              </span>
                            ),
                          )}
                        </div>
                      ) : (
                        <span className="text-sm text-gray-500">
                          No locations selected
                        </span>
                      )}
                    </div>
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
                          <Input
                            className="bg-gray-50 w-auto"
                            readOnly
                            value={
                              campaignDetails.mailingSchedule.mailingFrequency
                                .label || "Not specified"
                            }
                          />
                        </div>
                        <p className="text-sm text-gray-500">
                          Number of mailings sent within each frequency cycle
                        </p>
                      </div>

                      {campaignDetails.mailingSchedule.mailingDropWeeks.length >
                        0 && (
                        <div className="space-y-4">
                          <div className="text-sm font-medium text-gray-700">
                            Selected Mailing Weeks
                          </div>
                          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                            {Array.from(
                              {
                                length:
                                  campaignDetails.mailingSchedule
                                    .mailingFrequency.value || 0,
                              },
                              (_, index) => {
                                const weekNumber = index + 1;
                                const selectedWeek =
                                  campaignDetails.mailingSchedule.mailingDropWeeks.find(
                                    (w) => w.weekNumber === weekNumber,
                                  );
                                const isSelected = !!selectedWeek;

                                return (
                                  <div
                                    className={`p-4 rounded-lg border ${
                                      isSelected
                                        ? "bg-blue-100 border-blue-500"
                                        : "bg-gray-50 border-gray-200"
                                    }`}
                                    key={index}
                                  >
                                    <div className="flex flex-col items-center space-y-2">
                                      <Mail
                                        className={`w-6 h-6 ${
                                          isSelected
                                            ? "text-blue-600"
                                            : "text-gray-400"
                                        }`}
                                      />
                                      <div className="text-sm font-medium">
                                        Week {weekNumber}
                                      </div>
                                      {isSelected && selectedWeek && (
                                        <div className="text-xs text-blue-600 font-medium">
                                          {selectedWeek.mailingCount.toLocaleString()}{" "}
                                          mailings
                                        </div>
                                      )}
                                    </div>
                                  </div>
                                );
                              },
                            )}
                          </div>
                        </div>
                      )}

                      <div className="mt-6">
                        <div className="text-sm text-gray-700">
                          <span className="font-medium">Total Prospects:</span>{" "}
                          {campaignDetails.totalProspects.toLocaleString()}
                        </div>
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
                    value={campaignDetails.filters.campaignTarget.value}
                  >
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem
                        disabled
                        id="include_active_customers_only"
                        value="include_active_customers_only"
                      />
                      <Label htmlFor="include_active_customers_only">
                        Active customers only
                      </Label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem
                        disabled
                        id="include_prospects_only"
                        value="include_prospects_only"
                      />
                      <Label htmlFor="include_prospects_only">
                        Prospects only
                      </Label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem
                        disabled
                        id="include_prospects_and_customers"
                        value="include_prospects_and_customers"
                      />
                      <Label htmlFor="include_prospects_and_customers">
                        Both customers and prospects
                      </Label>
                    </div>
                  </RadioGroup>
                </div>

                <div className="space-y-4">
                  <h3 className="text-lg font-semibold">Address Type</h3>
                  <RadioGroup
                    className="space-y-2"
                    value={campaignDetails.filters.addressType.value}
                  >
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem
                        disabled
                        id="include_residential_only"
                        value="include_residential_only"
                      />
                      <Label htmlFor="include_residential_only">Residential</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem
                        disabled
                        id="include_commercial_only"
                        value="include_commercial_only"
                      />
                      <Label htmlFor="include_commercial_only">Commercial</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                      <RadioGroupItem
                        disabled
                        id="include_both_residential_and_commercial"
                        value="include_both_residential_and_commercial"
                      />
                      <Label htmlFor="include_both_residential_and_commercial">Both Residential & Commercial</Label>
                    </div>
                  </RadioGroup>
                </div>

                {campaignDetails.showTagSelector &&
                  campaignDetails.filters.tags.length > 0 && (
                    <div className="space-y-4">
                      <h3 className="text-lg font-semibold">Selected Tags</h3>
                      <div className="bg-gray-50 px-3 py-2 rounded-md border">
                        <div className="flex flex-wrap gap-2">
                          {campaignDetails.filters.tags.map(
                            (tag: Tag, index: number) => (
                              <span
                                className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full"
                                key={index}
                              >
                                {tag.name}
                              </span>
                            ),
                          )}
                        </div>
                      </div>
                    </div>
                  )}

                {campaignDetails.showDemographicTargets && (
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold">
                      Demographic Targets
                    </h3>
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                      <div className="space-y-2">
                        <Label>Prospect Age Range</Label>
                        <div className="flex gap-2">
                          <Input
                            className="bg-gray-50"
                            placeholder="Min"
                            readOnly
                            type="number"
                            value={getDemographicTargetValue(
                              campaignDetails.filters.demographicTargets,
                              "prospect_min_age",
                            )}
                          />
                          <Input
                            className="bg-gray-50"
                            placeholder="Max"
                            readOnly
                            type="number"
                            value={getDemographicTargetValue(
                              campaignDetails.filters.demographicTargets,
                              "prospect_max_age",
                            )}
                          />
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label>Minimum Estimated Income</Label>
                        <Input
                          className="bg-gray-50"
                          readOnly
                          value={
                            getDemographicTargetValue(
                              campaignDetails.filters.demographicTargets,
                              "min_estimated_income",
                            ) || "Not specified"
                          }
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>Minimum Home Age</Label>
                        <Input
                          className="bg-gray-50"
                          readOnly
                          value={
                            getDemographicTargetValue(
                              campaignDetails.filters.demographicTargets,
                              "home_min_age",
                            ) || "Not specified"
                          }
                        />
                      </div>
                    </div>
                  </div>
                )}

                {campaignDetails.showCustomerRestrictionCriteria && (
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold">
                      Existing Customer Restriction Criteria
                    </h3>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="flex items-center space-x-2">
                        <Checkbox
                          checked={
                            campaignDetails.filters.customerRestrictionCriteria.some(
                              (criteria: CustomerRestrictionCriterion) =>
                                criteria.name === "customer_max_ltv" &&
                                criteria.value === "5000",
                            ) || false
                          }
                          disabled
                          id="excludeLTV"
                        />
                        <Label htmlFor="excludeLTV">
                          Exclude LTV greater than $5000
                        </Label>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <span>
                              <Info className="w-4 h-4 text-gray-500 cursor-pointer" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p className="text-sm">
                              Excludes customers whose total combined purchases
                              across all invoices exceed $5,000.
                            </p>
                          </TooltipContent>
                        </Tooltip>
                      </div>

                      <div className="flex items-center space-x-2">
                        <Checkbox
                          checked={
                            campaignDetails.filters.customerRestrictionCriteria.some(
                              (criteria: CustomerRestrictionCriterion) =>
                                criteria.name === "club_members_inclusion" &&
                                criteria.value === "exclude_club_members",
                            ) || false
                          }
                          disabled
                          id="ExcludeClubMembers"
                        />
                        <Label htmlFor="ExcludeClubMembers">
                          Exclude club members
                        </Label>
                      </div>

                      <div className="flex items-center space-x-2">
                        <Checkbox
                          checked={
                            campaignDetails.filters.customerRestrictionCriteria.some(
                              (criteria: CustomerRestrictionCriterion) =>
                                criteria.name ===
                                  "customer_installations_inclusion" &&
                                criteria.value ===
                                  "exclude_customer_installations",
                            ) || false
                          }
                          disabled
                          id="excludeCustomerInstallations"
                        />
                        <Label htmlFor="excludeCustomerInstallations">
                          Exclude customer installations
                        </Label>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <span>
                              <Info className="w-4 h-4 text-gray-500 cursor-pointer" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p className="text-sm">
                              Excludes customers who have any single invoice of
                              $2,500 or more. We assume an invoice of $2,500 or
                              more indicates installation or replacement.
                            </p>
                          </TooltipContent>
                        </Tooltip>
                      </div>
                    </div>
                  </div>
                )}

                <div className="space-y-4">
                  <h3 className="text-lg font-semibold">
                    Selected Postal Codes
                  </h3>

                  <div className="block md:hidden space-y-4">
                    {campaignDetails.postalCodeLimits.map((postalCodeLimit) => (
                      <div
                        className="rounded-md border p-4 shadow-sm"
                        key={postalCodeLimit.postalCode}
                      >
                        <div className="mb-2 text-sm font-medium text-gray-700">
                          Postal Code:{" "}
                          <span className="font-normal">
                            {postalCodeLimit.postalCode}
                          </span>
                        </div>
                        <div className="mb-2 text-sm font-medium text-gray-700">
                          Selected Prospects:{" "}
                          <span className="font-normal">
                            {postalCodeLimit.limit.toLocaleString()}
                          </span>
                        </div>
                      </div>
                    ))}
                  </div>

                  <div className="hidden md:block relative w-full overflow-auto rounded-md border shadow-sm">
                    <table className="w-full caption-bottom text-sm">
                      <thead>
                        <tr className="bg-gray-50">
                          <th className="p-4 text-left align-middle font-medium">
                            Postal Code
                          </th>
                          <th className="p-4 text-left align-middle font-medium">
                            Selected Prospects
                          </th>
                        </tr>
                      </thead>
                      <tbody className="[&_tr:last-child]:border-0">
                        {campaignDetails.postalCodeLimits.map(
                          (postalCodeLimit) => (
                            <tr
                              className="border-b bg-white"
                              key={postalCodeLimit.postalCode}
                            >
                              <td className="p-4 align-middle font-medium">
                                {postalCodeLimit.postalCode}
                              </td>
                              <td className="p-4 align-middle">
                                {postalCodeLimit.limit.toLocaleString()}
                              </td>
                            </tr>
                          ),
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>

                {(campaignDetails.canBeStopped ||
                  campaignDetails.canBePaused) && (
                  <div className="flex justify-end space-x-4 pt-6">
                    {campaignDetails.canBeStopped && (
                      <Button
                        disabled={loading}
                        onClick={handleShowStopModal}
                        variant="default"
                      >
                        Stop Campaign
                      </Button>
                    )}
                    {campaignDetails.canBePaused && (
                      <Button
                        disabled={loading}
                        onClick={handleShowPauseModal}
                        variant="outline"
                      >
                        Pause Campaign
                      </Button>
                    )}
                  </div>
                )}

                {campaignDetails.canBeResumed && (
                  <div className="flex justify-end pt-6">
                    <Button
                      disabled={loading}
                      onClick={handleShowResumeModal}
                      variant="default"
                    >
                      Resume Campaign
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {campaignDetails.id > 0 && (
            <>
              <StopCampaignModal
                campaignId={campaignDetails.id}
                isOpen={showStopModal}
                onClose={handleCloseStopModal}
                onSuccess={handleStopModalSuccess}
              />

              <PauseCampaignModal
                campaignId={campaignDetails.id}
                isOpen={showPauseModal}
                onClose={handleClosePauseModal}
                onSuccess={handlePauseModalSuccess}
              />

              <ResumeCampaignModal
                campaignId={campaignDetails.id}
                isOpen={showResumeModal}
                onClose={handleCloseResumeModal}
                onSuccess={handleResumeModalSuccess}
              />
            </>
          )}
        </TooltipProvider>
      )}
    </MainPageWrapper>
  );
}

export default ViewCampaignDetailsPage;
