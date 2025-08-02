import React, { useState } from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Copy } from "lucide-react";
import LoadingIndicator from "@/components/LoadingIndicator/LoadingIndicator";
import InfiniteScroll from "react-infinite-scroll-component";
import useEmailTemplateVariableSelector from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/useEmailTemplatesVariableSelector";

interface EmailTemplateVariableSelectorProps {
  onVariableSelect: (value: string) => void;
}

const EmailTemplateVariableSelector: React.FC<
  EmailTemplateVariableSelectorProps
> = ({ onVariableSelect }) => {
  const {
    emailTemplateVariables,
    isLoadingInitial,
    hasMore,
    fetchNext,
    searchTerm,
    setSearchTerm,
  } = useEmailTemplateVariableSelector();

  const [isOpen, setIsOpen] = useState(false);

  const handleSelectVariable = (variable: string) => {
    onVariableSelect(variable);
    setIsOpen(false);
  };

  return (
    <div className="relative">
      <DropdownMenu onOpenChange={setIsOpen} open={isOpen}>
        <DropdownMenuTrigger asChild>
          <Button variant="outline">Insert Variable</Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
          align="end"
          className="w-full sm:w-auto sm:min-w-[315px] sm:max-w-[400px] max-w-[90vw] p-2 bg-white max-h-[50vh] overflow-auto rounded-md shadow-lg border border-gray-300sm :ml-0 ml-4"
          side="bottom"
        >
          <ShowIfHasAccess requiredRoles={["ROLE_SUPER_ADMIN"]}>
            <p className="px-2 py-1 text-sm font-medium">Template Variables</p>
            <p className="px-2 py-1 text-sm text-gray-500 mb-2">
              Insert these variables into your email template.
            </p>
            <input
              className="w-full p-2 mb-2 border rounded-md text-sm"
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Search variables..."
              value={searchTerm}
            />
            <div
              className="overflow-auto p-2 h-[200px]"
              id="infinite-scroller-parent"
            >
              {isLoadingInitial ? (
                <LoadingIndicator isFullScreen={false} />
              ) : (
                <InfiniteScroll
                  dataLength={emailTemplateVariables.length}
                  endMessage={
                    <p className="text-center py-2">No more variables.</p>
                  }
                  hasMore={hasMore}
                  loader={<p className="text-center py-2">Loading...</p>}
                  next={fetchNext}
                  scrollableTarget="infinite-scroller-parent"
                >
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="text-left font-semibold text-gray-700">
                        <th className="pb-2 px-2">Variable</th>
                        <th className="pb-2 px-2">Description</th>
                        <th className="pb-2 px-2"></th>
                      </tr>
                    </thead>
                    <tbody>
                      {emailTemplateVariables.map(
                        ({ id, name, description }) => (
                          <tr className="border-t text-gray-900" key={id}>
                            <td className="p-2 font-medium">{name}</td>
                            <td className="p-2 text-gray-700">{description}</td>
                            <td className="p-2 text-center">
                              <button
                                aria-label="Select entity"
                                className="p-1 rounded-md"
                                onClick={() => handleSelectVariable(name)}
                              >
                                <Copy className="h-4 w-4" />
                              </button>
                            </td>
                          </tr>
                        ),
                      )}
                    </tbody>
                  </table>
                </InfiniteScroll>
              )}
            </div>
          </ShowIfHasAccess>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  );
};

export default EmailTemplateVariableSelector;
