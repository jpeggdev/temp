"use client";

import * as React from "react";
import { format } from "date-fns";
import { DayPicker } from "react-day-picker";
import "react-day-picker/dist/style.css";
import { DateTime } from "luxon";

import { Calendar as CalendarIcon, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { cn } from "@/components/ui/lib/utils";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

interface DatePickerProps {
  /** Current date value in the picker */
  value?: Date | null;

  /** Called when a new date is selected */
  onChange?: (date: Date | null) => void;

  /** Whether to show time selection */
  showTimeSelect?: boolean;

  /** Placeholder text if no date is selected */
  placeholder?: string;

  /** The earliest date selectable */
  minDate?: Date | null;

  /** Disable user interaction? */
  disabled?: boolean;

  /** The timezone to use for the date picker */
  timeZone?: string | null;
}

/**
 * Generate an array of 30-minute increments in 12-hour format,
 * e.g. ["12:00 AM", "12:30 AM", "1:00 AM", ... "11:30 PM"].
 */
function generateHalfHourIncrements12h(): string[] {
  const times: string[] = [];
  for (let hour = 0; hour < 24; hour++) {
    const d1 = new Date(2000, 0, 1, hour, 0);
    const d2 = new Date(2000, 0, 1, hour, 30);

    times.push(format(d1, "h:mm aa"));
    times.push(format(d2, "h:mm aa"));
  }
  return times;
}

export function DatePicker({
  value,
  onChange,
  showTimeSelect = false,
  placeholder = "Pick a date",
  minDate,
  disabled = false,
  timeZone = null,
}: DatePickerProps) {
  const [time12h, setTime12h] = React.useState(() => {
    if (value) {
      if (timeZone) {
        const dt = DateTime.fromJSDate(value).setZone(timeZone);
        return dt.toFormat("h:mm a");
      }
      return format(value, "h:mm aa");
    }
    return "12:00 PM";
  });

  React.useEffect(() => {
    if (value && timeZone) {
      const dt = DateTime.fromJSDate(value).setZone(timeZone);
      setTime12h(dt.toFormat("h:mm a"));
    } else if (value) {
      setTime12h(format(value, "h:mm aa"));
    }
  }, [value, timeZone]);

  const timeOptions = React.useMemo(() => generateHalfHourIncrements12h(), []);

  const handleDateSelect = (selectedDate?: Date) => {
    if (!selectedDate) {
      onChange?.(null);
      return;
    }

    let finalDate: Date;

    if (showTimeSelect && time12h) {
      const timeParts = /(\d+):(\d+) (AM|PM)/i.exec(time12h);
      if (timeParts) {
        let hours = parseInt(timeParts[1], 10);
        const minutes = parseInt(timeParts[2], 10);
        const meridian = timeParts[3].toUpperCase();

        if (meridian === "PM" && hours < 12) {
          hours += 12;
        } else if (meridian === "AM" && hours === 12) {
          hours = 0;
        }

        let dt = DateTime.fromJSDate(selectedDate);
        if (timeZone) {
          dt = dt.setZone(timeZone);
        }

        dt = dt.set({ hour: hours, minute: minutes });

        finalDate = dt.toUTC().toJSDate();
      } else {
        finalDate = selectedDate;
      }
    } else {
      if (timeZone) {
        const dt = DateTime.fromJSDate(selectedDate)
          .setZone(timeZone)
          .startOf("day");
        finalDate = dt.toUTC().toJSDate();
      } else {
        finalDate = selectedDate;
      }
    }

    onChange?.(finalDate);
  };

  const handleTimeChange = (newTime: string) => {
    setTime12h(newTime);

    if (value) {
      const timeParts = /(\d+):(\d+) (AM|PM)/i.exec(newTime);
      if (timeParts) {
        let hours = parseInt(timeParts[1], 10);
        const minutes = parseInt(timeParts[2], 10);
        const meridian = timeParts[3].toUpperCase();

        if (meridian === "PM" && hours < 12) {
          hours += 12;
        } else if (meridian === "AM" && hours === 12) {
          hours = 0;
        }

        let dt = DateTime.fromJSDate(value);
        if (timeZone) {
          dt = dt.setZone(timeZone);
        }

        dt = dt.set({ hour: hours, minute: minutes });

        const newDate = dt.toUTC().toJSDate();
        onChange?.(newDate);
      }
    }
  };

  const displayText = value
    ? timeZone
      ? `${DateTime.fromJSDate(value)
          .setZone(timeZone)
          .toFormat(showTimeSelect ? "DDD h:mm a" : "DDD")}`
      : format(value, showTimeSelect ? "PPP h:mm aa" : "PPP")
    : placeholder;

  const displayedDate =
    value && timeZone
      ? DateTime.fromJSDate(value).setZone(timeZone).toJSDate()
      : value;

  return (
    <Popover>
      <PopoverTrigger asChild>
        <Button
          className={cn(
            "w-full justify-start text-left font-normal",
            !value && "text-muted-foreground",
            disabled && "opacity-50 cursor-not-allowed",
          )}
          disabled={disabled}
          variant="outline"
        >
          <CalendarIcon className="mr-2 h-4 w-4" />
          {displayText}
        </Button>
      </PopoverTrigger>
      <PopoverContent align="start" className="w-auto p-0 bg-white">
        <div className="p-3">
          <DayPicker
            disabled={minDate ? [{ before: minDate }] : undefined}
            mode="single"
            onSelect={handleDateSelect}
            selected={displayedDate ?? undefined}
            showOutsideDays
          />

          {showTimeSelect && (
            <div className="flex items-center gap-2 px-3 py-2 border-t mt-2">
              <Clock className="h-4 w-4" />
              <Select
                disabled={disabled}
                onValueChange={handleTimeChange}
                value={time12h}
              >
                <SelectTrigger className="w-[120px]">
                  <SelectValue placeholder="Pick a time" />
                </SelectTrigger>
                <SelectContent>
                  {timeOptions.map((t) => (
                    <SelectItem key={t} value={t}>
                      {t}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          )}
        </div>
      </PopoverContent>
    </Popover>
  );
}
