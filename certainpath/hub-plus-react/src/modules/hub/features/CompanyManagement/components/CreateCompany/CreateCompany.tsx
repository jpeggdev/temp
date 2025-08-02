import React from "react";
import { useDispatch, useSelector } from "react-redux";
import { createCompanyAction } from "../../slices/companiesSlice";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { RootState } from "../../../../../../app/rootReducer";
import { useValidation } from "../../../../../../hooks/useValidation";
import CertainPathTextInput from "../../../../../../components/CertainPathTextInput/CertainPathTextInput";
import CertainPathButton from "../../../../../../components/CertainPathButton/CertainPathButton";
import { useNotification } from "../../../../../../context/NotificationContext";
import validationSchema from "./validationSchema";
import { useNavigate } from "react-router-dom";

const CreateCompany: React.FC = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { loading, error } = useSelector((state: RootState) => state.companies);
  const { showNotification } = useNotification();

  const { values, errors, handleChange, validateForm } = useValidation(
    {
      companyName: "",
      websiteUrl: "",
      salesforceId: "",
      intacctId: "",
      companyEmail: "",
    },
    validationSchema,
  );

  const isFormValid = () =>
    !Object.values(errors).some((error) => error !== null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm()) {
      dispatch(
        createCompanyAction(
          {
            companyName: values.companyName,
            websiteUrl: values.websiteUrl || null,
            salesforceId: values.salesforceId || null,
            intacctId: values.intacctId || null,
            companyEmail: values.companyEmail || null,
          },
          (newCompany) => {
            showNotification(
              "Successfully created company!",
              "The company information has been created.",
              "success",
            );
            navigate(`/admin/companies/${newCompany.uuid}/edit`);
          },
        ),
      );
    }
  };

  return (
    <MainPageWrapper error={error} loading={loading} title="Create Company">
      <form className="space-y-6" onSubmit={handleSubmit}>
        <CertainPathTextInput
          error={errors.companyName}
          name="companyName"
          onChange={handleChange}
          placeholder="Company Name"
          value={values.companyName}
        />
        <CertainPathTextInput
          error={errors.websiteUrl}
          name="websiteUrl"
          onChange={handleChange}
          placeholder="Website URL"
          value={values.websiteUrl}
        />
        <CertainPathTextInput
          error={errors.salesforceId}
          name="salesforceId"
          onChange={handleChange}
          placeholder="Salesforce ID"
          value={values.salesforceId}
        />
        <CertainPathTextInput
          error={errors.intacctId}
          name="intacctId"
          onChange={handleChange}
          placeholder="Intacct ID"
          value={values.intacctId}
        />
        <CertainPathTextInput
          error={errors.companyEmail}
          name="companyEmail"
          onChange={handleChange}
          placeholder="Company Email"
          value={values.companyEmail}
        />
        <CertainPathButton disabled={!isFormValid()} type="submit">
          Create
        </CertainPathButton>
      </form>
    </MainPageWrapper>
  );
};

export default CreateCompany;
