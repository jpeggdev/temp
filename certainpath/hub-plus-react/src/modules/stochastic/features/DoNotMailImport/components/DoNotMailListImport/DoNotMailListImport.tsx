import React from "react";
import { FileUploader } from "react-drag-drop-files";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { useDoNotMailListImport } from "../../hook/useDoNotMailListImport";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/Badge/Badge";
import ConfirmAddToDoNotMailListModal from "@/modules/stochastic/features/DoNotMailImport/components/ConfirmAddToDoNotMailListModal/ConfirmAddToDoNotMailListModal";
import ConfirmationDialog from "@/components/ConfirmationDialog/ConfirmationDialog";

export const DoNotMailListImport: React.FC = () => {
  const {
    file,
    uploadErrorModalState,
    matchesCount,
    loadingCreate,
    loadingUpload,
    addressesMatches,
    handleUpload,
    uploadProgress,
    handleFileChange,
    isConfirmAddModalOpen,
    showUploadErrorModal,
    handleAddToDoNotMailList,
    handleCancelAddToDoNotMailList,
    handleCloseConfirmModalButtonClick,
    handleAddToDoNotMailListButtonClick,
  } = useDoNotMailListImport();

  const FILE_TYPES = ["CSV", "XLSX", "XLS"];

  return (
    <MainPageWrapper error={null} loading={false} title="Do Not Mail Import">
      <div>
        <p className="mb-6 text-gray-700">
          Import addresses that should be excluded from mailings
        </p>

        <FileUploader
          handleChange={handleFileChange}
          maxSize={100}
          name="file"
          types={FILE_TYPES}
        />

        {file && (
          <p className="mt-4 text-sm text-gray-600">
            Selected file: <span className="font-medium">{file.name}</span>
          </p>
        )}

        <Button
          className={`mt-6 ${
            loadingUpload
              ? "bg-gray-400 cursor-not-allowed"
              : "bg-blue-500 hover:bg-blue-600"
          }`}
          disabled={!file || loadingUpload}
          onClick={() => handleUpload({ file: file! })}
        >
          {loadingUpload ? "Uploading..." : "Upload"}
        </Button>

        {loadingUpload && (
          <div className="mt-4" id="upload-status">
            <div className="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
              <div
                className="bg-blue-500 h-2.5 rounded-full transition-all duration-200"
                style={{ width: `${uploadProgress}%` }}
              />
            </div>
            <p className="mt-2 text-sm text-gray-500 text-right">
              {uploadProgress}% Complete
            </p>
          </div>
        )}

        {(addressesMatches ?? []).length > 0 && (
          <div className="mt-10">
            <div className="flex justify-end items-center gap-4 mb-6">
              <Button
                className=""
                onClick={handleAddToDoNotMailListButtonClick}
                variant="default"
              >
                Add to Do Not Mail List
              </Button>
              <Button
                className="bg-gray-200 hover:bg-gray-300 text-gray-800 "
                onClick={handleCancelAddToDoNotMailList}
                variant="outline"
              >
                Cancel
              </Button>
            </div>

            <div className="w-full overflow-auto border border-gray-200 rounded-md shadow-sm max-h-[450px]">
              <table className="w-full caption-bottom text-sm min-w-max">
                <thead className="[&_tr]:border-b">
                  <tr className="bg-gray-50/75">
                    <th className="p-4 align-middle font-semibold text-muted-foreground text-left">
                      Address 1
                    </th>
                    <th className="p-4 align-middle font-semibold text-muted-foreground text-left">
                      Address 2
                    </th>
                    <th className="p-4 align-middle font-semibold text-muted-foreground text-left">
                      City
                    </th>
                    <th className="p-4 align-middle font-semibold text-muted-foreground text-left">
                      State
                    </th>
                    <th className="p-4 align-middle font-semibold text-muted-foreground text-left">
                      Postal Code
                    </th>
                    <th className="p-4 align-middle font-semibold text-muted-foreground text-left">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody className="[&_tr:last-child]:border-0">
                  {(addressesMatches ?? []).map((addr, index) => (
                    <tr
                      className="border-b transition-colors hover:bg-muted/50"
                      key={index}
                    >
                      <td className="p-4 align-middle text-left whitespace-nowrap">
                        {addr.address1 || "N/A"}
                      </td>
                      <td className="p-4 align-middle text-left whitespace-nowrap">
                        {addr.address2 || "N/A"}
                      </td>
                      <td className="p-4 align-middle text-left whitespace-nowrap">
                        {addr.city || "N/A"}
                      </td>
                      <td className="p-4 align-middle text-left whitespace-nowrap">
                        {addr.state || "N/A"}
                      </td>
                      <td className="p-4 align-middle text-left whitespace-nowrap">
                        {addr.zip || "N/A"}
                      </td>
                      <td className="p-4 align-middle text-left whitespace-nowrap">
                        <Badge
                          className={`whitespace-nowrap text-xs font-semibold px-2 py-1 rounded-full pointer-events-none ${
                            addr.isMatched
                              ? "bg-green-100 text-green-800"
                              : "bg-gray-200 text-gray-700"
                          }`}
                          variant="secondary"
                        >
                          {addr.isMatched ? "Matched" : "Not Matched"}
                        </Badge>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>

      <ConfirmAddToDoNotMailListModal
        handleConfirm={handleAddToDoNotMailList}
        isAdding={loadingCreate}
        isOpen={isConfirmAddModalOpen}
        matchesCount={matchesCount}
        onClose={handleCloseConfirmModalButtonClick}
      />

      {/*Upload Error Modal*/}
      <ConfirmationDialog
        cancelMessage={uploadErrorModalState.cancelMessage}
        confirmMessage={uploadErrorModalState.confirmMessage}
        dialogInstructionFinalQuestion={
          uploadErrorModalState.instructionFinalQuestion
        }
        dialogInstructionItems={uploadErrorModalState.instructionItems}
        dialogInstructionTitle={uploadErrorModalState.instructionTitle}
        dialogTitleContent={uploadErrorModalState.titleContent}
        isOpen={showUploadErrorModal}
        onClose={uploadErrorModalState.cancelHandler}
        onConfirm={uploadErrorModalState.confirmHandler}
      />
    </MainPageWrapper>
  );
};
