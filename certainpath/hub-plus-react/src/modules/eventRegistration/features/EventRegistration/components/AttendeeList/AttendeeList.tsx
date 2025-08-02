import React, { useState } from "react";
import { useFormContext, useFieldArray } from "react-hook-form";
import { AttendeeItem } from "@/modules/eventRegistration/features/EventRegistration/components/AttendeeItem/AttendeeItem";
import AddAttendees from "@/modules/eventRegistration/features/EventRegistration/components/AddAttendees/AddAttendees";
import { EventRegistrationFormData } from "@/modules/eventRegistration/features/EventRegistration/hooks/useEventRegistration";

export default function AttendeeList() {
  const { control } = useFormContext<EventRegistrationFormData>();
  const [duplicateEmailError, setDuplicateEmailError] = useState<string | null>(
    null,
  );
  const { fields, remove, append } = useFieldArray({
    control,
    name: "attendees",
  });

  function handleAddAttendee(attendee: {
    firstName: string;
    lastName: string;
    email?: string;
  }) {
    if (attendee.email) {
      const normalizedEmail = attendee.email.toLowerCase().trim();
      const isDuplicate = fields.some(
        (field) =>
          field.email && field.email.toLowerCase().trim() === normalizedEmail,
      );
      if (isDuplicate) {
        setDuplicateEmailError(
          `An attendee with email ${attendee.email} has already been added.`,
        );
        return;
      }
    }
    setDuplicateEmailError(null);
    append({
      ...attendee,
      isSelected: true,
    });
  }

  return (
    <div className="space-y-8">
      <div className="space-y-4">
        <div className="flex justify-between items-center">
          <h3 className="text-lg font-semibold">Attendees</h3>
        </div>
        <div className="space-y-4">
          {fields.map((field, index) => {
            const handleRemove = () => {
              remove(index);
            };
            return (
              <AttendeeItem
                attendee={field}
                canRemove={true}
                index={index}
                key={field.id}
                onRemove={handleRemove}
              />
            );
          })}
        </div>
      </div>
      <AddAttendees
        duplicateEmailError={duplicateEmailError}
        onAddAttendee={handleAddAttendee}
        onClearDuplicateEmailError={() => setDuplicateEmailError(null)}
      />
    </div>
  );
}
