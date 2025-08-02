import React from "react";
import Modal from "react-modal";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Button } from "@/components/Button/Button";
import { BulkBatchStatusOption } from "@/api/fetchBulkBatchStatusDetailsMetadata/types";

interface BatchStatusModalProps {
  isModalOpen: boolean;
  onCloseButtonClick: () => void;
  selectedStatus: string;
  batchStatusOptions: BulkBatchStatusOption[];
  onStatusChange: (status: string) => void;
  onSaveButtonClick: () => void;
  isSaveButtonDisabled: boolean;
  isSaving: boolean;
}

const UpdateBatchStatusModal: React.FC<BatchStatusModalProps> = ({
  isModalOpen,
  onCloseButtonClick,
  selectedStatus,
  batchStatusOptions,
  onStatusChange,
  onSaveButtonClick,
  isSaveButtonDisabled,
  isSaving,
}) => {
  return (
    <Modal
      isOpen={isModalOpen}
      onRequestClose={onCloseButtonClick}
      style={{
        content: {
          top: "50%",
          left: "50%",
          right: "auto",
          bottom: "auto",
          transform: "translate(-50%, -50%)",
          borderRadius: "8px",
          padding: "24px",
          width: "400px",
          background: "white",
          boxShadow: "0 10px 25px rgba(0,0,0,.3)",
        },
        overlay: { backgroundColor: "rgba(0,0,0,0.3)", zIndex: 9999 },
      }}
    >
      <h3 className="text-xl font-semibold text-gray-900">
        Update Batch Status
      </h3>

      <div className="py-4">
        <RadioGroup
          className="flex flex-col gap-4"
          onValueChange={onStatusChange}
          value={selectedStatus}
        >
          {batchStatusOptions.map(({ id, label, description, enabled }) => (
            <div className="flex items-center space-x-2" key={id}>
              <RadioGroupItem disabled={!enabled} id={id} value={id} />
              <label className="text-sm font-medium text-gray-700" htmlFor={id}>
                <span className="font-bold">{label}</span> <br />
                <span className="text-gray-500 text-xs">{description}</span>
              </label>
            </div>
          ))}
        </RadioGroup>
      </div>

      <div className="mt-6 flex justify-end space-x-3">
        <Button onClick={onCloseButtonClick} size="default" variant="plain">
          Cancel
        </Button>

        <Button
          disabled={isSaveButtonDisabled}
          onClick={onSaveButtonClick}
          variant="default"
        >
          {isSaving ? "Saving..." : "Save"}
        </Button>
      </div>
    </Modal>
  );
};

export default UpdateBatchStatusModal;
