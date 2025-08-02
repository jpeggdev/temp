import { useState, useEffect } from "react";
import { fetchEmailTemplateVariables } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateVariables/fetchEmailTemplateVariablesApi";
import { EmailTemplateVariable } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateVariables/types";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";

const useEmailTemplateVariableSelector = () => {
  const [searchTerm, setSearchTerm] = useState("");
  const [emailTemplateVariables, setEmailTemplateVariables] = useState<
    EmailTemplateVariable[]
  >([]);
  const [isLoadingInitial, setIsLoadingInitial] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const debouncedSearchTerm = useDebouncedValue(searchTerm, 500);

  const fetchEntities = async ({
    searchTerm,
    page,
    pageSize,
  }: {
    searchTerm: string;
    page: number;
    pageSize: number;
  }) => {
    try {
      const response = await fetchEmailTemplateVariables({
        searchTerm,
        page,
        pageSize,
        sortBy: "name",
        sortOrder: "ASC",
      });

      if (!response) {
        throw new Error("No response received");
      }

      const { data: emailTemplateVariablesResponse } = response;
      const totalCount =
        response.meta?.totalCount ?? emailTemplateVariablesResponse.length;

      return {
        data: emailTemplateVariablesResponse.map((c) => ({
          id: c.id,
          name: c.name,
          description: c.description,
        })),
        totalCount,
      };
    } catch (error) {
      console.error("Error fetching entities:", error);
      return { data: [], totalCount: 0 };
    }
  };

  const loadEntities = (pageNumber: number, sTerm: string) => {
    if (pageNumber === 1) {
      setIsLoadingInitial(true);
    }

    fetchEntities({
      searchTerm: sTerm,
      page: pageNumber,
      pageSize: 3,
    })
      .then((res) => {
        const newData = res.data;
        setEmailTemplateVariables((prev) =>
          pageNumber === 1 ? newData : [...prev, ...newData],
        );
        if (newData.length < 3) {
          setHasMore(false);
        }
      })
      .catch(() => {
        setHasMore(false);
      })
      .finally(() => {
        if (pageNumber === 1) {
          setIsLoadingInitial(false);
        }
      });
  };

  useEffect(() => {
    setEmailTemplateVariables([]);
    setPage(1);
    setHasMore(true);
    loadEntities(1, debouncedSearchTerm);
  }, [debouncedSearchTerm]);

  const fetchNext = () => {
    const nextPage = page + 1;
    setPage(nextPage);
    loadEntities(nextPage, debouncedSearchTerm);
  };

  return {
    emailTemplateVariables,
    isLoadingInitial,
    hasMore,
    fetchNext,
    searchTerm,
    setSearchTerm,
  };
};

export default useEmailTemplateVariableSelector;
