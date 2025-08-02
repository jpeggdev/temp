"use client";

import React, { useEffect, useMemo, useState } from "react";
import { useDispatch, useSelector } from "react-redux";

import { AppDispatch } from "../../../../../../app/store";
import { RootState } from "../../../../../../app/rootReducer";
import { MyCompanyProfile } from "../../../../../../api/getMyCompanyProfile/types";

import { Switch } from "@headlessui/react";
import clsx from "clsx";
import {
  fetchCompanyProfileAction,
  updateCompanyProfileAction,
} from "../../slices/companyProfileSlice";
import { UpdateMyCompanyProfileRequest } from "../../../../../../api/updateMyCompanyProfile/types";
import { useNotification } from "../../../../../../context/NotificationContext";
import countryList from "react-select-country-list";
import { allCountries } from "country-region-data";
import Select from "react-select";
import permissionsService from "@/services/permissionsService";

interface OptionType {
  label: string;
  value: string;
}

const CompanyProfile: React.FC = () => {
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();

  // Local state for form inputs
  const [formData, setFormData] = useState<MyCompanyProfile>({
    companyName: "",
    companyEmail: "",
    websiteUrl: "",
    addressLine1: "",
    addressLine2: "",
    city: "",
    state: "",
    country: "US", // Default to US
    zipCode: "",
    isMailingAddressSame: true,
    mailingAddressLine1: "",
    mailingAddressLine2: "",
    mailingState: "",
    mailingCountry: "US", // Default to US
    mailingZipCode: "",
  });

  // Local state for country and state options
  const countryOptions = useMemo(() => countryList().getData(), []);
  const [stateOptions, setStateOptions] = useState<OptionType[]>([]);
  const [mailingStateOptions, setMailingStateOptions] = useState<OptionType[]>(
    [],
  );

  // Select data from the Redux store
  const { companyProfile, loading } = useSelector(
    (state: RootState) => state.companyProfile,
  );

  // Get permissions
  const { hasPermission } = permissionsService();
  const canManageCompanyOwn = hasPermission("CAN_MANAGE_COMPANY_OWN");

  useEffect(() => {
    // Fetch company profile on component mount
    dispatch(fetchCompanyProfileAction());
  }, [dispatch]);

  useEffect(() => {
    if (companyProfile) {
      setFormData(companyProfile);
    }
  }, [companyProfile]);

  // Update state options when country changes
  useEffect(() => {
    if (formData.country) {
      const countryData = allCountries.find((c) => c[1] === formData.country);
      if (countryData && countryData[2] && countryData[2].length > 0) {
        const options = countryData[2].map((region) => ({
          value: region[1], // region code
          label: region[0], // region name
        }));
        setStateOptions(options);
      } else {
        setStateOptions([]);
      }
    } else {
      setStateOptions([]);
    }
    // Reset state when country changes
    setFormData((prevData) => ({ ...prevData, state: "" }));
  }, [formData.country]);

  // Update mailing state options when mailing country changes
  useEffect(() => {
    if (formData.mailingCountry) {
      const countryData = allCountries.find(
        (c) => c[1] === formData.mailingCountry,
      );
      if (countryData && countryData[2] && countryData[2].length > 0) {
        const options = countryData[2].map((region) => ({
          value: region[1],
          label: region[0],
        }));
        setMailingStateOptions(options);
      } else {
        setMailingStateOptions([]);
      }
    } else {
      setMailingStateOptions([]);
    }
    // Reset mailing state when mailing country changes
    setFormData((prevData) => ({ ...prevData, mailingState: "" }));
  }, [formData.mailingCountry]);

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>,
  ) => {
    if (!canManageCompanyOwn) return;
    const { name, value } = e.target;
    let newValue: string | boolean = value;
    if (e.target instanceof HTMLInputElement && e.target.type === "checkbox") {
      newValue = e.target.checked;
    }
    setFormData((prevData) => ({
      ...prevData,
      [name]: newValue,
    }));
  };

  const handleSwitchChange = (value: boolean) => {
    if (!canManageCompanyOwn) return;
    setFormData((prevData) => ({
      ...prevData,
      isMailingAddressSame: value,
    }));
  };

  const handleCountryChange = (selectedOption: OptionType | null) => {
    if (!canManageCompanyOwn) return;
    setFormData((prevData) => ({
      ...prevData,
      country: selectedOption ? selectedOption.value : "",
    }));
  };

  const handleStateChange = (selectedOption: OptionType | null) => {
    if (!canManageCompanyOwn) return;
    setFormData((prevData) => ({
      ...prevData,
      state: selectedOption ? selectedOption.value : "",
    }));
  };

  const handleMailingCountryChange = (selectedOption: OptionType | null) => {
    if (!canManageCompanyOwn) return;
    setFormData((prevData) => ({
      ...prevData,
      mailingCountry: selectedOption ? selectedOption.value : "",
    }));
  };

  const handleMailingStateChange = (selectedOption: OptionType | null) => {
    if (!canManageCompanyOwn) return;
    setFormData((prevData) => ({
      ...prevData,
      mailingState: selectedOption ? selectedOption.value : "",
    }));
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!canManageCompanyOwn) return;

    // Prepare data for update (exclude unnecessary fields)
    const {
      companyName,
      companyEmail,
      websiteUrl,
      addressLine1,
      addressLine2,
      city,
      state,
      country,
      zipCode,
      isMailingAddressSame,
      mailingAddressLine1,
      mailingAddressLine2,
      mailingState,
      mailingCountry,
      mailingZipCode,
    } = formData;

    const updateData: UpdateMyCompanyProfileRequest = {
      companyName,
      companyEmail,
      websiteUrl,
      addressLine1,
      addressLine2,
      city,
      state,
      country,
      zipCode,
      isMailingAddressSame,
      mailingAddressLine1: isMailingAddressSame ? null : mailingAddressLine1,
      mailingAddressLine2: isMailingAddressSame ? null : mailingAddressLine2,
      mailingState: isMailingAddressSame ? null : mailingState,
      mailingCountry: isMailingAddressSame ? null : mailingCountry,
      mailingZipCode: isMailingAddressSame ? null : mailingZipCode,
    };

    dispatch(
      updateCompanyProfileAction(updateData, () => {
        showNotification(
          "Successfully updated company profile!",
          "Your company profile information has been updated.",
          "success",
        );
      }),
    );
  };

  return (
    <div className="border-b border-gray-900/10 pb-12">
      <h2 className="text-base font-semibold leading-7 text-gray-900">
        Company Profile
      </h2>
      <p className="mt-1 text-sm leading-6 text-gray-600">
        Manage your company information and settings.
      </p>

      <form onSubmit={handleSubmit}>
        <div className="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
          {/* Company Name */}
          <div className="sm:col-span-4">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="companyName"
            >
              Company Name
            </label>
            <div className="mt-2">
              <input
                autoComplete="organization"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                disabled={!canManageCompanyOwn}
                id="companyName"
                name="companyName"
                onChange={handleInputChange}
                type="text"
                value={formData.companyName}
              />
            </div>
          </div>

          {/* Company Email */}
          <div className="sm:col-span-4">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="companyEmail"
            >
              Company Email
            </label>
            <div className="mt-2">
              <input
                autoComplete="email"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                disabled={!canManageCompanyOwn}
                id="companyEmail"
                name="companyEmail"
                onChange={handleInputChange}
                type="email"
                value={formData.companyEmail || ""}
              />
            </div>
          </div>

          {/* Website URL */}
          <div className="sm:col-span-4">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="websiteUrl"
            >
              Website URL
            </label>
            <div className="mt-2">
              <input
                autoComplete="url"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                disabled={!canManageCompanyOwn}
                id="websiteUrl"
                name="websiteUrl"
                onChange={handleInputChange}
                placeholder="https://www.company.com"
                type="url"
                value={formData.websiteUrl || ""}
              />
            </div>
          </div>

          {/* Address Line 1 */}
          <div className="sm:col-span-6">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="addressLine1"
            >
              Address Line 1
            </label>
            <div className="mt-2">
              <input
                autoComplete="street-address"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                disabled={!canManageCompanyOwn}
                id="addressLine1"
                name="addressLine1"
                onChange={handleInputChange}
                placeholder="8111 Mainland Dr."
                type="text"
                value={formData.addressLine1 || ""}
              />
            </div>
          </div>

          {/* Address Line 2 */}
          <div className="sm:col-span-6">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="addressLine2"
            >
              Address Line 2
            </label>
            <div className="mt-2">
              <input
                autoComplete="address-line2"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                disabled={!canManageCompanyOwn}
                id="addressLine2"
                name="addressLine2"
                onChange={handleInputChange}
                placeholder="Suite 100"
                type="text"
                value={formData.addressLine2 || ""}
              />
            </div>
          </div>

          {/* City */}
          <div className="sm:col-span-2">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="city"
            >
              City
            </label>
            <div className="mt-2">
              <input
                autoComplete="address-level2"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                disabled={!canManageCompanyOwn}
                id="city"
                name="city"
                onChange={handleInputChange}
                placeholder="San Antonio"
                type="text"
                value={formData.city || ""}
              />
            </div>
          </div>

          {/* Country */}
          <div className="sm:col-span-2">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="country"
            >
              Country
            </label>
            <div className="mt-2">
              <Select
                className="react-select-container"
                classNamePrefix="react-select"
                id="country"
                isDisabled={!canManageCompanyOwn}
                name="country"
                onChange={handleCountryChange}
                options={countryOptions}
                value={
                  countryOptions.find(
                    (option) => option.value === formData.country,
                  ) || null
                }
              />
            </div>
          </div>

          {/* State */}
          <div className="sm:col-span-2">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="state"
            >
              State / Province
            </label>
            <div className="mt-2">
              {stateOptions.length > 0 ? (
                <Select
                  className="react-select-container"
                  classNamePrefix="react-select"
                  id="state"
                  isDisabled={!canManageCompanyOwn}
                  name="state"
                  onChange={handleStateChange}
                  options={stateOptions}
                  value={
                    stateOptions.find(
                      (option) => option.value === formData.state,
                    ) || null
                  }
                />
              ) : (
                <input
                  autoComplete="address-level1"
                  className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  disabled={!canManageCompanyOwn}
                  id="state"
                  name="state"
                  onChange={handleInputChange}
                  placeholder="State / Province"
                  type="text"
                  value={formData.state || ""}
                />
              )}
            </div>
          </div>

          {/* Zip Code */}
          <div className="sm:col-span-2">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="zipCode"
            >
              ZIP / Postal Code
            </label>
            <div className="mt-2">
              <input
                autoComplete="postal-code"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                disabled={!canManageCompanyOwn}
                id="zipCode"
                name="zipCode"
                onChange={handleInputChange}
                placeholder="78240"
                type="text"
                value={formData.zipCode || ""}
              />
            </div>
          </div>

          {/* Is Mailing Address Same */}
          <div className="sm:col-span-6">
            <label className="block text-sm font-medium leading-6 text-gray-900">
              Mailing Address Same
            </label>
            <div className="mt-2 flex items-center">
              <Switch
                checked={formData.isMailingAddressSame}
                className={clsx(
                  formData.isMailingAddressSame
                    ? "bg-indigo-600"
                    : "bg-gray-200",
                  "relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2",
                )}
                disabled={!canManageCompanyOwn}
                onChange={handleSwitchChange}
              >
                <span
                  className={clsx(
                    formData.isMailingAddressSame
                      ? "translate-x-6"
                      : "translate-x-1",
                    "inline-block h-4 w-4 transform rounded-full bg-white transition-transform",
                  )}
                />
              </Switch>
              <span className="ml-3 text-sm text-gray-600">
                {formData.isMailingAddressSame
                  ? "Same as Address"
                  : "Different Mailing Address"}
              </span>
            </div>
          </div>

          {/* Mailing Address Fields */}
          {!formData.isMailingAddressSame && (
            <>
              {/* Mailing Address Line 1 */}
              <div className="sm:col-span-6">
                <label
                  className="block text-sm font-medium leading-6 text-gray-900"
                  htmlFor="mailingAddressLine1"
                >
                  Mailing Address Line 1
                </label>
                <div className="mt-2">
                  <input
                    autoComplete="street-address"
                    className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    disabled={!canManageCompanyOwn}
                    id="mailingAddressLine1"
                    name="mailingAddressLine1"
                    onChange={handleInputChange}
                    placeholder="7224 Eckhert Rd"
                    type="text"
                    value={formData.mailingAddressLine1 || ""}
                  />
                </div>
              </div>

              {/* Mailing Address Line 2 */}
              <div className="sm:col-span-6">
                <label
                  className="block text-sm font-medium leading-6 text-gray-900"
                  htmlFor="mailingAddressLine2"
                >
                  Mailing Address Line 2
                </label>
                <div className="mt-2">
                  <input
                    autoComplete="address-line2"
                    className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    disabled={!canManageCompanyOwn}
                    id="mailingAddressLine2"
                    name="mailingAddressLine2"
                    onChange={handleInputChange}
                    placeholder="Suite 200"
                    type="text"
                    value={formData.mailingAddressLine2 || ""}
                  />
                </div>
              </div>

              {/* Mailing Country */}
              <div className="sm:col-span-2">
                <label
                  className="block text-sm font-medium leading-6 text-gray-900"
                  htmlFor="mailingCountry"
                >
                  Mailing Country
                </label>
                <div className="mt-2">
                  <Select
                    className="react-select-container"
                    classNamePrefix="react-select"
                    id="mailingCountry"
                    isDisabled={!canManageCompanyOwn}
                    name="mailingCountry"
                    onChange={handleMailingCountryChange}
                    options={countryOptions}
                    value={
                      countryOptions.find(
                        (option) => option.value === formData.mailingCountry,
                      ) || null
                    }
                  />
                </div>
              </div>

              {/* Mailing State */}
              <div className="sm:col-span-2">
                <label
                  className="block text-sm font-medium leading-6 text-gray-900"
                  htmlFor="mailingState"
                >
                  Mailing State / Province
                </label>
                <div className="mt-2">
                  {mailingStateOptions.length > 0 ? (
                    <Select
                      className="react-select-container"
                      classNamePrefix="react-select"
                      id="mailingState"
                      isDisabled={!canManageCompanyOwn}
                      name="mailingState"
                      onChange={handleMailingStateChange}
                      options={mailingStateOptions}
                      value={
                        mailingStateOptions.find(
                          (option) => option.value === formData.mailingState,
                        ) || null
                      }
                    />
                  ) : (
                    <input
                      autoComplete="address-level1"
                      className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                      disabled={!canManageCompanyOwn}
                      id="mailingState"
                      name="mailingState"
                      onChange={handleInputChange}
                      placeholder="Mailing State / Province"
                      type="text"
                      value={formData.mailingState || ""}
                    />
                  )}
                </div>
              </div>

              {/* Mailing Zip Code */}
              <div className="sm:col-span-2">
                <label
                  className="block text-sm font-medium leading-6 text-gray-900"
                  htmlFor="mailingZipCode"
                >
                  Mailing ZIP / Postal Code
                </label>
                <div className="mt-2">
                  <input
                    autoComplete="postal-code"
                    className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    disabled={!canManageCompanyOwn}
                    id="mailingZipCode"
                    name="mailingZipCode"
                    onChange={handleInputChange}
                    placeholder="78238-1244"
                    type="text"
                    value={formData.mailingZipCode || ""}
                  />
                </div>
              </div>
            </>
          )}
        </div>

        {/* Save Button */}
        {canManageCompanyOwn && (
          <div className="mt-6 flex items-center justify-end gap-x-6">
            <button
              className="text-sm font-semibold leading-6 text-gray-900"
              disabled={loading}
              onClick={() => setFormData(companyProfile || formData)}
              type="button"
            >
              Cancel
            </button>
            <button
              className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:ring-indigo-600"
              disabled={loading}
              type="submit"
            >
              {loading ? "Saving..." : "Save"}
            </button>
          </div>
        )}
      </form>
    </div>
  );
};

export default CompanyProfile;
