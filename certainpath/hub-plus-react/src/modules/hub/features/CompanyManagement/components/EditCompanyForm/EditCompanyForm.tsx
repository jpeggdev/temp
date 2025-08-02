import React from "react";
import { FormValues } from "../EditCompany/EditCompany";
import CertainPathTextInput from "../../../../../../components/CertainPathTextInput/CertainPathTextInput";
import CertainPathButton from "../../../../../../components/CertainPathButton/CertainPathButton";
import CertainPathCheckbox from "../../../../../../components/CertainPathCheckbox/CertainPathCheckbox";

interface CompanyDetailsFormProps {
  values: FormValues;
  handleChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  handleSubmit: (e: React.FormEvent) => void;
  errors: { [key: string]: string | null | undefined };
  isFormValid: boolean;
}

const EditCompanyForm: React.FC<CompanyDetailsFormProps> = ({
  values,
  handleChange,
  handleSubmit,
  errors,
  isFormValid,
}) => {
  return (
    <form className="space-y-6" onSubmit={handleSubmit}>
      {/* Company Name */}
      <CertainPathTextInput
        error={errors.companyName}
        name="companyName"
        onChange={handleChange}
        placeholder="Company Name"
        value={values.companyName}
      />

      {/* Salesforce ID */}
      <CertainPathTextInput
        error={errors.salesforceId}
        name="salesforceId"
        onChange={handleChange}
        placeholder="Salesforce ID"
        value={values.salesforceId || ""}
      />

      {/* Intacct ID */}
      <CertainPathTextInput
        error={errors.intacctId}
        name="intacctId"
        onChange={handleChange}
        placeholder="Intacct ID"
        value={values.intacctId || ""}
      />

      {/* Company Email */}
      <CertainPathTextInput
        error={errors.companyEmail}
        name="companyEmail"
        onChange={handleChange}
        placeholder="Company Email"
        type="email"
        value={values.companyEmail || ""}
      />

      {/* Website URL */}
      <CertainPathTextInput
        error={errors.websiteUrl}
        name="websiteUrl"
        onChange={handleChange}
        placeholder="Website URL"
        type="url"
        value={values.websiteUrl || ""}
      />

      {/* Marketing Enabled */}
      <CertainPathCheckbox
        checked={values.marketingEnabled}
        label="Stochastic Client"
        name="marketingEnabled"
        onChange={handleChange}
      />

      <CertainPathButton disabled={!isFormValid} type="submit">
        Update Company
      </CertainPathButton>
    </form>
  );
};

export default EditCompanyForm;
