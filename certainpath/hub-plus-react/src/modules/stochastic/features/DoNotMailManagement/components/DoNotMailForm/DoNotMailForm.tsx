import React, { useState, FormEvent } from "react";

export interface DoNotMailFormValues {
  address1: string;
  address2: string;
  city: string;
  stateCode: string;
  postalCode: string;
  countryCode: string;
}

interface DoNotMailFormProps {
  initialData: DoNotMailFormValues;
  loading: boolean;
  onSubmit: (values: DoNotMailFormValues) => void;
  onSuccess?: () => void;
}

const DoNotMailForm: React.FC<DoNotMailFormProps> = ({
  initialData,
  loading,
  onSubmit,
  onSuccess,
}) => {
  const [formValues, setFormValues] =
    useState<DoNotMailFormValues>(initialData);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const name = e.target.name;
    let value = e.target.value;

    // Only allow up to 2 characters for stateCode or countryCode
    if ((name === "stateCode" || name === "countryCode") && value.length > 2) {
      value = value.slice(0, 2);
    }

    setFormValues((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    onSubmit(formValues);
    if (onSuccess) onSuccess();
  };

  // All fields except address2 must be non-empty
  const isValid =
    formValues.address1?.trim() !== "" &&
    formValues.city?.trim() !== "" &&
    formValues.stateCode?.trim() !== "" &&
    formValues.postalCode?.trim() !== "" &&
    formValues.countryCode?.trim() !== "";

  return (
    <form onSubmit={handleSubmit}>
      <div className="space-y-4">
        {/* Address1 */}
        <div>
          <label
            className="block text-sm font-medium text-gray-700"
            htmlFor="address1"
          >
            Address 1 <span className="text-red-500">*</span>
          </label>
          <input
            className="mt-1 block w-full border border-gray-300 rounded-md p-2"
            id="address1"
            name="address1"
            onChange={handleChange}
            type="text"
            value={formValues.address1}
          />
        </div>

        {/* Address2 */}
        <div>
          <label
            className="block text-sm font-medium text-gray-700"
            htmlFor="address2"
          >
            Address 2
          </label>
          <input
            className="mt-1 block w-full border border-gray-300 rounded-md p-2"
            id="address2"
            name="address2"
            onChange={handleChange}
            type="text"
            value={formValues.address2}
          />
        </div>

        {/* City */}
        <div>
          <label
            className="block text-sm font-medium text-gray-700"
            htmlFor="city"
          >
            City <span className="text-red-500">*</span>
          </label>
          <input
            className="mt-1 block w-full border border-gray-300 rounded-md p-2"
            id="city"
            name="city"
            onChange={handleChange}
            type="text"
            value={formValues.city}
          />
        </div>

        {/* State Code */}
        <div>
          <label
            className="block text-sm font-medium text-gray-700"
            htmlFor="stateCode"
          >
            State Code <span className="text-red-500">*</span>
          </label>
          <input
            className="mt-1 block w-full border border-gray-300 rounded-md p-2"
            id="stateCode"
            name="stateCode"
            onChange={handleChange}
            type="text"
            value={formValues.stateCode}
          />
        </div>

        {/* Postal Code */}
        <div>
          <label
            className="block text-sm font-medium text-gray-700"
            htmlFor="postalCode"
          >
            Postal Code <span className="text-red-500">*</span>
          </label>
          <input
            className="mt-1 block w-full border border-gray-300 rounded-md p-2"
            id="postalCode"
            name="postalCode"
            onChange={handleChange}
            type="text"
            value={formValues.postalCode}
          />
        </div>

        {/* Country Code */}
        <div>
          <label
            className="block text-sm font-medium text-gray-700"
            htmlFor="countryCode"
          >
            Country Code <span className="text-red-500">*</span>
          </label>
          <input
            className="mt-1 block w-full border border-gray-300 rounded-md p-2"
            id="countryCode"
            name="countryCode"
            onChange={handleChange}
            type="text"
            value={formValues.countryCode}
          />
        </div>
      </div>

      <div className="mt-6 flex justify-end">
        <button
          className={`px-4 py-2 rounded-md ${
            !isValid || loading
              ? "bg-gray-300 text-gray-500 cursor-not-allowed"
              : "bg-secondary text-white hover:bg-secondary-light"
          }`}
          disabled={!isValid || loading}
          type="submit"
        >
          {loading ? "Saving..." : "Save"}
        </button>
      </div>
    </form>
  );
};

export default DoNotMailForm;
