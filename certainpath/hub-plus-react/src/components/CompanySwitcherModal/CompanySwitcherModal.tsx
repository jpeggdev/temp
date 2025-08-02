import React, { useEffect, useState } from "react";
import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from "@headlessui/react";
import { CheckIcon } from "@heroicons/react/24/outline";
import { Company } from "../../api/fetchMyCompanies/types";
import { fetchMyCompanies } from "../../api/fetchMyCompanies/fetchMyCompaniesApi";
import InfiniteScroll from "react-infinite-scroll-component";
import { useDebouncedValue } from "../../hooks/useDebouncedValue";
import { useLocation } from "react-router-dom";
import { getSectionFromPath } from "@/utils/navigationHelpers";

interface CompanySwitcherModalProps {
  open: boolean;
  onClose: () => void;
}

const CompanySwitcherModal: React.FC<CompanySwitcherModalProps> = ({
  open,
  onClose,
}) => {
  const [companies, setCompanies] = useState<Company[]>([]);
  const [selectedCompany, setSelectedCompany] = useState<Company | null>(null);
  const [page, setPage] = useState<number>(1);
  const [hasMore, setHasMore] = useState<boolean>(true);
  const [search, setSearch] = useState<string>("");
  const debouncedSearch = useDebouncedValue(search, 500);

  const location = useLocation();

  useEffect(() => {
    if (open) {
      setCompanies([]);
      setPage(1);
      setHasMore(true);
      loadCompanies(1, debouncedSearch);
    }
  }, [debouncedSearch, open]);

  useEffect(() => {
    if (!open) {
      setCompanies([]);
      setPage(1);
      setHasMore(true);
      setSelectedCompany(null);
      setSearch("");
    }
  }, [open]);

  const loadCompanies = (pageNumber: number, searchQuery: string) => {
    fetchMyCompanies(pageNumber, searchQuery)
      .then((response) => {
        const newCompanies = response.data;

        setCompanies((prevCompanies) =>
          pageNumber === 1 ? newCompanies : [...prevCompanies, ...newCompanies],
        );

        if (newCompanies.length < 10) {
          setHasMore(false);
        }
      })
      .catch((error) => {
        console.error("Error fetching companies:", error);
        setHasMore(false);
      });
  };

  const fetchNextCompanies = () => {
    const nextPage = page + 1;
    setPage(nextPage);
    loadCompanies(nextPage, debouncedSearch);
  };

  const handleCompanySelect = (company: Company) => {
    setSelectedCompany(company);
  };

  const handleConfirmSelection = () => {
    if (selectedCompany) {
      localStorage.setItem("selectedCompanyUuid", selectedCompany.companyUuid);

      const sectionInfo = getSectionFromPath(location.pathname);
      if (sectionInfo) {
        window.location.href = sectionInfo.defaultRoute;
      } else {
        window.location.href = "/";
      }

      onClose();
    }
  };

  return (
    <Dialog className="relative z-30" onClose={onClose} open={open}>
      <DialogBackdrop className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      <div className="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <DialogPanel className="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <div>
              <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                <CheckIcon
                  aria-hidden="true"
                  className="h-6 w-6 text-green-600"
                />
              </div>
              <div className="mt-3 text-center sm:mt-5">
                <DialogTitle
                  as="h3"
                  className="text-base font-semibold leading-6 text-gray-900"
                >
                  Switch Company
                </DialogTitle>
                <div className="mt-2">
                  <p className="text-sm text-gray-500">
                    Select the company you want to switch to:
                  </p>

                  <div className="my-4">
                    <input
                      className="w-full border border-gray-300 rounded-md px-4 py-2"
                      onChange={(e) => setSearch(e.target.value)}
                      placeholder="Search for a company..."
                      type="text"
                      value={search}
                    />
                  </div>

                  <InfiniteScroll
                    dataLength={companies.length}
                    endMessage={<p>No more companies to load</p>}
                    hasMore={hasMore}
                    height={300}
                    loader={<h4>Loading more...</h4>}
                    next={fetchNextCompanies}
                  >
                    <ul className="mt-4 space-y-2">
                      {companies.map((company) => (
                        <li key={company.companyUuid}>
                          <button
                            className={`w-full text-left px-4 py-2 rounded-md ${
                              selectedCompany?.companyUuid ===
                              company.companyUuid
                                ? "bg-secondary dark:bg-primary text-white"
                                : "bg-gray-50 hover:bg-gray-100"
                            }`}
                            onClick={() => handleCompanySelect(company)}
                          >
                            <div className="text-sm font-medium">
                              {company.companyName}
                            </div>
                            {company.intacctId && (
                              <div className="text-xs text-gray-500">
                                Intacct ID: {company.intacctId}
                              </div>
                            )}
                          </button>
                        </li>
                      ))}
                    </ul>
                  </InfiniteScroll>
                </div>
              </div>
            </div>
            <div className="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
              <button
                className="inline-flex w-full justify-center rounded-md dark:bg-primary dark:hover:bg-primary-light bg-secondary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary-light sm:col-start-2"
                disabled={!selectedCompany}
                onClick={handleConfirmSelection}
                type="button"
              >
                Confirm
              </button>
              <button
                className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                onClick={onClose}
                type="button"
              >
                Cancel
              </button>
            </div>
          </DialogPanel>
        </div>
      </div>
    </Dialog>
  );
};

export default CompanySwitcherModal;
