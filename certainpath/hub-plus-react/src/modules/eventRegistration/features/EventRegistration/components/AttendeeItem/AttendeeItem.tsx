"use client";

import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { Trash2, Check } from "lucide-react";
import { useFormContext, Controller, useWatch } from "react-hook-form";

interface Attendee {
  id?: string;
  firstName: string;
  lastName: string;
  email?: string;
  isSelected: boolean;
  specialRequests?: string;
  isNew?: boolean;
}

interface AttendeeItemProps {
  attendee: Attendee;
  index: number;
  canRemove: boolean;
  onRemove: () => void;
}

export function AttendeeItem({
  attendee,
  index,
  canRemove,
  onRemove,
}: AttendeeItemProps): React.ReactElement {
  const { control } = useFormContext();

  const isSelected = useWatch({
    control,
    name: `attendees.${index}.isSelected`,
    defaultValue: attendee.isSelected,
  });

  return (
    <div className="border rounded-lg p-4 space-y-4">
      <div className="flex justify-between items-start">
        <div className="flex items-center">
          <Controller
            control={control}
            name={`attendees.${index}.isSelected`}
            render={({ field }) => (
              <Checkbox
                checked={field.value}
                color="default"
                icon={<Check className="text-white" />}
                id={`attendee-${index}-selected`}
                onCheckedChange={field.onChange}
              />
            )}
          />
          <Label
            className="ml-2 font-medium"
            htmlFor={`attendee-${index}-selected`}
          >
            {attendee.isNew ? "New Attendee" : "Employee"} {index + 1}
          </Label>
        </div>

        {canRemove && (
          <Button onClick={onRemove} size="sm" type="button" variant="ghost">
            <Trash2 className="h-4 w-4 text-destructive" />
          </Button>
        )}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor={`attendee-${index}-firstName`}>First Name</Label>
          <Controller
            control={control}
            name={`attendees.${index}.firstName`}
            render={({ field }) => (
              <Input
                disabled={!isSelected}
                id={`attendee-${index}-firstName`}
                {...field}
              />
            )}
          />
        </div>

        <div>
          <Label htmlFor={`attendee-${index}-lastName`}>Last Name</Label>
          <Controller
            control={control}
            name={`attendees.${index}.lastName`}
            render={({ field }) => (
              <Input
                disabled={!isSelected}
                id={`attendee-${index}-lastName`}
                {...field}
              />
            )}
          />
        </div>
      </div>

      <div>
        <Label htmlFor={`attendee-${index}-email`}>Email</Label>
        <Controller
          control={control}
          name={`attendees.${index}.email`}
          render={({ field }) => (
            <Input
              disabled={!isSelected}
              id={`attendee-${index}-email`}
              type="email"
              {...field}
              value={field.value || ""}
            />
          )}
        />
      </div>

      <div>
        <Label htmlFor={`attendee-${index}-specialRequests`}>
          Special Requests (Optional)
        </Label>
        <Controller
          control={control}
          name={`attendees.${index}.specialRequests`}
          render={({ field }) => (
            <Textarea
              disabled={!isSelected}
              id={`attendee-${index}-specialRequests`}
              placeholder="Dietary restrictions, accessibility needs, etc."
              {...field}
              value={field.value || ""}
            />
          )}
        />
      </div>
    </div>
  );
}
