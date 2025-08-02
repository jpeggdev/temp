"use client";

import React, { useState } from "react";
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
import { Mail } from "lucide-react";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { TooltipProvider } from "@/components/ui/tooltip";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

interface ZipCode {
  code: string;
  avgSale: number;
  availableProspects: number;
  selectedProspects: string;
  filteredProspects: number;
}

interface FilterCriteria {
  prospectAge: { min: string; max: string };
  prospectIncome: string;
  homeAge: string;
  excludeClubMembers: boolean;
  excludeLTV: boolean;
  excludeInstallCustomers: boolean;
  excludeKnownCustomers: boolean;
}

interface FormData {
  campaignName: string;
  audience: string;
  addressType: string;
  description: string;
  phoneNumber: string;
  startDate: string;
  endDate: string;
  mailingFrequency: string;
  selectedMailingWeeks: number[];
  locations: number[];
  filterCriteria: FilterCriteria;
  zipCodes: ZipCode[];
}

const CampaignForm = () => {
  // Original prospect data (simulated database)
  const originalZipData = [
    {
      code: "30033",
      avgSale: 2500,
      totalProspects: 4413,
      prospects: Array.from({ length: 4413 }, () => ({
        age: 30 + Math.floor(Math.random() * 60),
        income: 30000 + Math.floor(Math.random() * 120000),
        homeAge: 1 + Math.floor(Math.random() * 50),
        isClubMember: Math.random() > 0.9,
        isHighLTV: Math.random() > 0.9,
        isInstallCustomer: Math.random() > 0.9,
        isKnownCustomer: Math.random() > 0.8,
      })),
    },
  ];

  const [formData, setFormData] = useState<FormData>({
    campaignName: "",
    audience: "prospects",
    addressType: "include_residential_only",
    description: "",
    phoneNumber: "",
    startDate: "",
    endDate: "",
    mailingFrequency: "",
    selectedMailingWeeks: [],
    locations: [],
    filterCriteria: {
      prospectAge: { min: "40", max: "90" },
      prospectIncome: "50000",
      homeAge: "5",
      excludeClubMembers: false,
      excludeLTV: false,
      excludeInstallCustomers: false,
      excludeKnownCustomers: false,
    },
    zipCodes: [
      {
        code: "30033",
        avgSale: 2500,
        availableProspects: 4413,
        selectedProspects: "",
        filteredProspects: 4413,
      },
      {
        code: "30030",
        avgSale: 2300,
        availableProspects: 7850,
        selectedProspects: "",
        filteredProspects: 7850,
      },
      {
        code: "30316",
        avgSale: 2100,
        availableProspects: 3879,
        selectedProspects: "",
        filteredProspects: 3879,
      },
      {
        code: "30317",
        avgSale: 1800,
        availableProspects: 6784,
        selectedProspects: "",
        filteredProspects: 6784,
      },
    ],
  });

  const [isFiltering, setIsFiltering] = useState(false);
  const [filtersApplied, setFiltersApplied] = useState(false);
  const [filtersDirty, setFiltersDirty] = useState(false);

  const handleInputChange = (field: keyof FormData, value: string) => {
    if (field === "mailingFrequency") {
      // When changing duration, filter out any selected weeks that are beyond the new duration
      const newDuration = parseInt(value);
      setFormData((prev) => ({
        ...prev,
        [field]: value,
        selectedMailingWeeks: prev.selectedMailingWeeks.filter(
          (week) => week < newDuration,
        ),
      }));
    } else {
      setFormData((prev) => ({
        ...prev,
        [field]: value,
      }));
    }
  };

  const handleFilterChange = (
    field: keyof FilterCriteria,
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

  const applyFilters = () => {
    setIsFiltering(true);
    setFiltersDirty(false);

    setTimeout(() => {
      const newZipCodes = formData.zipCodes.map((zip) => {
        const originalData = originalZipData.find(
          (oz) => oz.code === zip.code,
        ) || { prospects: [] };

        const filteredCount = originalData.prospects.filter((prospect) => {
          const ageInRange =
            (!formData.filterCriteria.prospectAge.min ||
              prospect.age >=
                parseInt(formData.filterCriteria.prospectAge.min)) &&
            (!formData.filterCriteria.prospectAge.max ||
              prospect.age <=
                parseInt(formData.filterCriteria.prospectAge.max));

          const meetsIncome =
            !formData.filterCriteria.prospectIncome ||
            prospect.income >= parseInt(formData.filterCriteria.prospectIncome);

          const meetsHomeAge =
            !formData.filterCriteria.homeAge ||
            prospect.homeAge >= parseInt(formData.filterCriteria.homeAge);

          let passesExclusions = true;
          if (formData.audience !== "prospects") {
            passesExclusions = !(
              (formData.filterCriteria.excludeClubMembers &&
                prospect.isClubMember) ||
              (formData.filterCriteria.excludeLTV && prospect.isHighLTV) ||
              (formData.filterCriteria.excludeInstallCustomers &&
                prospect.isInstallCustomer) ||
              (formData.filterCriteria.excludeKnownCustomers &&
                prospect.isKnownCustomer)
            );
          }

          return ageInRange && meetsIncome && meetsHomeAge && passesExclusions;
        }).length;

        return {
          ...zip,
          filteredProspects: filteredCount,
          selectedProspects: "",
        };
      });

      setFormData((prev) => ({
        ...prev,
        zipCodes: newZipCodes,
      }));
      setIsFiltering(false);
      setFiltersApplied(true);
    }, 500);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log("Form submitted:", formData);
  };

  const showExclusionFilters = formData.audience !== "prospects";

  const toggleMailingWeek = (weekIndex: number) => {
    setFormData((prev) => {
      const currentWeeks = prev.selectedMailingWeeks;
      let newWeeks;

      if (currentWeeks.includes(weekIndex)) {
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

  return (
    <TooltipProvider>
      <div className="max-w-4xl mx-auto p-4">
        <form className="space-y-8" onSubmit={handleSubmit}>
          <Card>
            <CardHeader>
              <CardTitle>Create New Campaign</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Campaign Details */}
              <div className="space-y-4">
                <h3 className="text-lg font-semibold">Campaign Details</h3>
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label>Campaign Name</Label>
                    <Input
                      onChange={(e) =>
                        handleInputChange("campaignName", e.target.value)
                      }
                      value={formData.campaignName}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label>Phone Number</Label>
                    <Input
                      onChange={(e) =>
                        handleInputChange("phoneNumber", e.target.value)
                      }
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
                    value={formData.description}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Location</Label>
                  <Input
                    onChange={(e) =>
                      handleInputChange("locations", e.target.value)
                    }
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
                  </div>
                </div>
              </div>

              {/* Mailing Schedule */}
              <Card>
                <CardHeader>
                  <CardTitle>Mailing Schedule</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-6">
                    <div className="space-y-4">
                      <div className="flex items-center gap-2">
                        <Label>Campaign Duration</Label>
                        <Select
                          onValueChange={(value) =>
                            handleInputChange("mailingFrequency", value)
                          }
                          value={formData.mailingFrequency}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select campaign duration" />
                          </SelectTrigger>
                          <SelectContent>
                            {[6, 7, 8, 9, 10].map((week) => (
                              <SelectItem key={week} value={week.toString()}>
                                {week} weeks
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                    </div>

                    <div className="grid grid-cols-5 gap-4">
                      {Array.from(
                        { length: parseInt(formData.mailingFrequency) || 6 },
                        (_, index) => (
                          <div
                            className={`
                            p-4 rounded-lg border cursor-pointer transition-all
                            ${
                              formData.selectedMailingWeeks.includes(index)
                                ? "bg-blue-100 border-blue-500"
                                : "hover:bg-gray-50"
                            }
                          `}
                            key={index}
                            onClick={() => toggleMailingWeek(index)}
                          >
                            <div className="flex flex-col items-center space-y-2">
                              <Mail
                                className={`w-6 h-6 ${formData.selectedMailingWeeks.includes(index) ? "text-blue-600" : "text-gray-400"}`}
                              />
                              <div className="text-sm font-medium">
                                Week {index + 1}
                              </div>
                              {formData.selectedMailingWeeks.includes(index) &&
                                hasAppliedFilters() && (
                                  <div className="text-xs text-blue-600 font-medium">
                                    {getProspectsPerMailing().toLocaleString()}{" "}
                                    mailings
                                  </div>
                                )}
                              {formData.selectedMailingWeeks.includes(index) &&
                                !hasAppliedFilters() && (
                                  <div className="text-xs text-gray-500">
                                    Apply filters first
                                  </div>
                                )}
                            </div>
                          </div>
                        ),
                      )}
                    </div>

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

              {/* Campaign Target Selection */}
              <div className="space-y-4">
                <h3 className="text-lg font-semibold">
                  Select your Campaign Target
                </h3>
                <RadioGroup
                  className="space-y-2"
                  onValueChange={(value) =>
                    handleInputChange("audience", value)
                  }
                  value={formData.audience}
                >
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem id="customers" value="customers" />
                    <Label htmlFor="customers">Active customers only</Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem id="prospects" value="prospects" />
                    <Label htmlFor="prospects">Prospects only</Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem id="both" value="both" />
                    <Label htmlFor="both">Both customers and prospects</Label>
                  </div>
                </RadioGroup>
              </div>

              <div className="space-y-4">
                <h3 className="text-lg font-semibold">Address Type</h3>
                <RadioGroup
                  className="space-y-2"
                  onValueChange={(value) =>
                    handleInputChange("addressType", value)
                  }
                  value={formData.addressType}
                >
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem
                      id="residential"
                      value="include_residential_only"
                    />
                    <Label htmlFor="residential">Residential</Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem
                      id="commercial"
                      value="include_commercial_only"
                    />
                    <Label htmlFor="commercial">Commercial</Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem
                      id="both_residential_and_commercial"
                      value="include_both_residential_and_commercial"
                    />
                    <Label htmlFor="both_residential_and_commercial">
                      Both Residential & Commercial
                    </Label>
                  </div>
                </RadioGroup>
              </div>

              {/* Demographic Targets */}
              <div className="space-y-4">
                <h3 className="text-lg font-semibold">
                  Select your Demographic Targets
                </h3>
                <div className="grid grid-cols-3 gap-4">
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
                    <Label>Minimum Income</Label>
                    <Input
                      onChange={(e) =>
                        handleFilterChange("prospectIncome", e.target.value)
                      }
                      placeholder="Enter amount"
                      type="number"
                      value={formData.filterCriteria.prospectIncome}
                    />
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

              {/* Existing Customer Restriction Criteria */}
              {showExclusionFilters && (
                <div className="space-y-4">
                  <h3 className="text-lg font-semibold">
                    Existing Customer Restriction Criteria
                  </h3>
                  <div className="grid grid-cols-2 gap-4">
                    <div className="flex items-center space-x-2">
                      <Checkbox
                        checked={formData.filterCriteria.excludeClubMembers}
                        id="excludeClubMembers"
                        onCheckedChange={(checked) =>
                          handleFilterChange("excludeClubMembers", checked)
                        }
                      />
                      <Label htmlFor="excludeClubMembers">
                        Exclude Club Accounts
                      </Label>
                    </div>

                    <div className="flex items-center space-x-2">
                      <Checkbox
                        checked={formData.filterCriteria.excludeLTV}
                        id="excludeLTV"
                        onCheckedChange={(checked) =>
                          handleFilterChange("excludeLTV", checked)
                        }
                      />
                      <Label htmlFor="excludeLTV">
                        Exclude customers with LTV &gt; $5,000
                      </Label>
                    </div>

                    <div className="flex items-center space-x-2">
                      <Checkbox
                        checked={
                          formData.filterCriteria.excludeInstallCustomers
                        }
                        id="excludeInstallCustomers"
                        onCheckedChange={(checked) =>
                          handleFilterChange("excludeInstallCustomers", checked)
                        }
                      />
                      <Label htmlFor="excludeInstallCustomers">
                        Exclude Install/Replacement Customers
                      </Label>
                    </div>

                    <div className="flex items-center space-x-2">
                      <Checkbox
                        checked={formData.filterCriteria.excludeKnownCustomers}
                        id="excludeKnownCustomers"
                        onCheckedChange={(checked) =>
                          handleFilterChange("excludeKnownCustomers", checked)
                        }
                      />
                      <Label htmlFor="excludeKnownCustomers">
                        Exclude Known Customers
                      </Label>
                    </div>
                  </div>
                </div>
              )}

              {/* Filter Actions */}
              <div className="flex justify-end items-center gap-4">
                {filtersDirty && (
                  <span className="text-yellow-600 text-sm">
                    Filters have changed. Please reapply filters to update
                    prospect counts.
                  </span>
                )}
                <Button
                  disabled={isFiltering}
                  onClick={applyFilters}
                  type="button"
                  variant={filtersDirty ? "default" : "outline"}
                >
                  {isFiltering ? "Applying Filters..." : "Apply Filters"}
                </Button>
              </div>

              {/* Zip Codes */}
              <div className="space-y-4">
                <h3 className="text-lg font-semibold">Select Zip Codes</h3>
                <div className="border rounded-lg">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Zip Code</TableHead>
                        <TableHead>Avg Sales</TableHead>
                        <TableHead>Households</TableHead>
                        <TableHead className="text-right">
                          Select Prospects
                        </TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {formData.zipCodes.map((zipCode, index) => (
                        <TableRow key={zipCode.code}>
                          <TableCell>{zipCode.code}</TableCell>
                          <TableCell>
                            ${zipCode.avgSale.toLocaleString()}
                          </TableCell>
                          <TableCell>
                            {isFiltering ? (
                              <span className="text-gray-400">
                                Calculating...
                              </span>
                            ) : (
                              zipCode.filteredProspects.toLocaleString()
                            )}
                          </TableCell>
                          <TableCell className="text-right">
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
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              </div>

              <CardFooter className="flex justify-end space-x-4">
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
                  Create Campaign
                </Button>
              </CardFooter>
            </CardContent>
          </Card>
        </form>
      </div>
    </TooltipProvider>
  );
};

export default CampaignForm;
