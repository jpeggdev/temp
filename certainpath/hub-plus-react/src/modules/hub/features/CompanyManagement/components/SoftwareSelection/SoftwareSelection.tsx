import React, { useState, useEffect } from "react";
import { RadioGroup } from "@headlessui/react";
import { useDispatch } from "react-redux";
import { updateFieldServiceSoftwareAction } from "../../slices/companiesSlice";
import clsx from "clsx";
import { FieldServiceSoftware } from "../../../../../../api/getEditCompanyDetails/types";
import { useNotification } from "../../../../../../context/NotificationContext";

interface SoftwareSelectionProps {
  fieldServiceSoftwareList: FieldServiceSoftware[];
  selectedSoftwareId: number | null;
  uuid: string;
}

const SoftwareSelection: React.FC<SoftwareSelectionProps> = ({
  fieldServiceSoftwareList,
  selectedSoftwareId,
  uuid,
}) => {
  const [selectedSoftware, setSelectedSoftware] =
    useState<FieldServiceSoftware | null>(null);
  const dispatch = useDispatch();
  const { showNotification } = useNotification();

  useEffect(() => {
    if (fieldServiceSoftwareList && selectedSoftwareId !== null) {
      const software = fieldServiceSoftwareList.find(
        (software) => software.id === selectedSoftwareId,
      );
      setSelectedSoftware(software || null);
    }
  }, [fieldServiceSoftwareList, selectedSoftwareId]);

  const handleSoftwareChange = async (software: FieldServiceSoftware) => {
    setSelectedSoftware(software);

    const updateDTO = {
      fieldServiceSoftwareId: software.id,
    };

    try {
      // Dispatch the action and wait for it to complete
      await dispatch(
        updateFieldServiceSoftwareAction(uuid, updateDTO, () => {
          showNotification(
            "Successfully updated software!",
            `The field service software has been updated to ${software.name}.`,
            "success",
          );
        }),
      );
    } catch {
      showNotification(
        "Failed to update software!",
        "There was an error updating the software.",
        "error",
      );
    }
  };

  return (
    <fieldset aria-label="Select a Field Service Software">
      <RadioGroup
        className="-space-y-px rounded-md bg-white"
        onChange={handleSoftwareChange}
        value={selectedSoftware}
      >
        {fieldServiceSoftwareList.map((software, idx) => (
          <RadioGroup.Option
            className={({ checked }) =>
              clsx(
                idx === 0 ? "rounded-tl-md rounded-tr-md" : "",
                idx === fieldServiceSoftwareList.length - 1
                  ? "rounded-bl-md rounded-br-md"
                  : "",
                "relative flex cursor-pointer border p-4 focus:outline-none",
                checked
                  ? "bg-indigo-50 border-indigo-200 z-10"
                  : "border-gray-200",
              )
            }
            key={software.id}
            value={software}
          >
            {({ active, checked }) => (
              <>
                <span
                  aria-hidden="true"
                  className={clsx(
                    checked
                      ? "bg-indigo-600 border-transparent"
                      : "bg-white border-gray-300",
                    active ? "ring-2 ring-offset-2 ring-indigo-500" : "",
                    "h-4 w-4 mt-0.5 cursor-pointer rounded-full border flex items-center justify-center",
                  )}
                >
                  <span className="rounded-full bg-white w-1.5 h-1.5" />
                </span>
                <span className="ml-3 flex flex-col">
                  <span
                    className={clsx(
                      "block text-sm font-medium",
                      checked ? "text-indigo-900" : "text-gray-900",
                    )}
                  >
                    {software.name}
                  </span>
                </span>
              </>
            )}
          </RadioGroup.Option>
        ))}
      </RadioGroup>
    </fieldset>
  );
};

export default SoftwareSelection;
