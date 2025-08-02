import React, { useEffect, useMemo, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useParams } from "react-router-dom";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { RootState } from "../../../../../../app/rootReducer";
import {
  fetchEditCompanyDetailsAction,
  editCompanyAction,
} from "../../slices/companiesSlice";
import CompanyDetailsForm from "../EditCompanyForm/EditCompanyForm";
import SoftwareSelection from "../SoftwareSelection/SoftwareSelection";
import TradesSelection from "../TradesSelection/TradesSelection";
import { useValidation } from "../../../../../../hooks/useValidation";
import { useNotification } from "../../../../../../context/NotificationContext";
import validationSchema from "../EditCompanyForm/validationSchema";
import clsx from "clsx";
import { EditCompanyDTO } from "../../../../../../api/editCompany/types";

export interface FormValues {
  companyName: string;
  salesforceId?: string | null;
  intacctId?: string | null;
  companyEmail?: string | null;
  websiteUrl?: string | null;
  marketingEnabled: boolean;
}

const EditCompany: React.FC = () => {
  const { uuid } = useParams<{ uuid: string }>();
  const dispatch = useDispatch();
  const { loading, error, selectedCompany, tradeList, companyTradeIds } =
    useSelector((state: RootState) => state.companies);

  const [currentTab, setCurrentTab] = useState("Company Details");
  const { showNotification } = useNotification();

  const { values, setValues, errors, handleChange, validateForm, isFormValid } =
    useValidation<FormValues>(
      {
        companyName: "",
        salesforceId: null,
        intacctId: null,
        companyEmail: null,
        websiteUrl: null,
        marketingEnabled: false,
      },
      validationSchema,
    );

  useEffect(() => {
    if (uuid) {
      dispatch(fetchEditCompanyDetailsAction(uuid));
    }
  }, [dispatch, uuid]);

  useEffect(() => {
    if (selectedCompany) {
      setValues({
        companyName: selectedCompany.companyName,
        salesforceId: selectedCompany.salesforceId,
        intacctId: selectedCompany.intacctId,
        companyEmail: selectedCompany.companyEmail,
        websiteUrl: selectedCompany.websiteUrl,
        marketingEnabled: selectedCompany.marketingEnabled || false,
      });
    }
  }, [selectedCompany, setValues]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm() && uuid) {
      const editCompanyDTO: EditCompanyDTO = {
        companyName: values.companyName,
        salesforceId: values.salesforceId,
        intacctId: values.intacctId,
        companyEmail: values.companyEmail,
        websiteUrl: values.websiteUrl,
        marketingEnabled: values.marketingEnabled,
      };

      dispatch(
        editCompanyAction(uuid, editCompanyDTO, (updatedCompany) => {
          showNotification(
            "Successfully updated company!",
            `The company ${updatedCompany.companyName} has been updated.`,
            "success",
          );
        }),
      );
    }
  };

  const tabs = [
    { name: "Company Details", current: currentTab === "Company Details" },
    { name: "Software", current: currentTab === "Software" },
    { name: "Trades", current: currentTab === "Trades" }, // New Trades Tab
  ];

  const manualBreadcrumbs = useMemo(() => {
    if (!uuid) return undefined;
    const companyName = selectedCompany?.companyName || `Company ${uuid}`;
    return [
      { path: "/admin/companies", label: "Companies" },
      {
        path: `/admin/companies/${uuid}/edit`,
        label: `Editing Company: ${companyName}`,
        clickable: false,
      },
    ];
  }, [uuid, selectedCompany]);

  if (!uuid) {
    return null;
  }

  return (
    <MainPageWrapper
      error={error}
      loading={loading}
      manualBreadcrumbs={manualBreadcrumbs}
      title="Edit Company"
    >
      {/* Tabs for smaller screens */}
      <div className="sm:hidden">
        <select
          className="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
          id="tabs"
          name="tabs"
          onChange={(e) => setCurrentTab(e.target.value)}
          value={currentTab}
        >
          {tabs.map((tab) => (
            <option key={tab.name} value={tab.name}>
              {tab.name}
            </option>
          ))}
        </select>
      </div>
      {/* Tabs for larger screens */}
      <div className="hidden sm:block mb-10">
        <div className="border-b border-gray-200">
          <nav aria-label="Tabs" className="-mb-px flex space-x-8">
            {tabs.map((tab) => (
              <button
                aria-current={tab.current ? "page" : undefined}
                className={clsx(
                  tab.current
                    ? "border-indigo-500 text-indigo-600"
                    : "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700",
                  "whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium",
                )}
                key={tab.name}
                onClick={() => setCurrentTab(tab.name)}
              >
                {tab.name}
              </button>
            ))}
          </nav>
        </div>
      </div>
      {/* Render the appropriate tab content */}
      {currentTab === "Company Details" && selectedCompany && (
        <CompanyDetailsForm
          errors={errors}
          handleChange={handleChange}
          handleSubmit={handleSubmit}
          isFormValid={isFormValid}
          values={values}
        />
      )}
      {currentTab === "Software" && selectedCompany && (
        <SoftwareSelection
          fieldServiceSoftwareList={
            selectedCompany.fieldServiceSoftwareList || []
          }
          selectedSoftwareId={selectedCompany.fieldServiceSoftwareId || null}
          uuid={uuid}
        />
      )}
      {currentTab === "Trades" && (
        <TradesSelection
          companyTradeIds={companyTradeIds}
          tradeList={tradeList}
          uuid={uuid}
        />
      )}
    </MainPageWrapper>
  );
};

export default EditCompany;
