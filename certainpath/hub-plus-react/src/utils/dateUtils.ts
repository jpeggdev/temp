export interface DateTimeImmutable {
  date: string;
  timezone_type: number;
  timezone: string;
}

export const formatDate = (
  dateInput: string | DateTimeImmutable | null,
): string => {
  if (!dateInput) return "";

  try {
    const dateString =
      typeof dateInput === "object" && "date" in dateInput
        ? dateInput.date
        : dateInput;

    const date = new Date(dateString);

    if (isNaN(date.getTime())) {
      return "";
    }

    return new Intl.DateTimeFormat("en-US", {
      month: "2-digit",
      day: "2-digit",
      year: "numeric",
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
      timeZone: "UTC",
    })
      .format(date)
      .replace(",", "");
  } catch (error) {
    console.error("Error formatting date:", error, dateInput);
    return "";
  }
};

/**
 * Formats a date range into a human-readable string
 * @param startDate ISO date string for the start date
 * @param endDate ISO date string for the end date
 * @returns Formatted date range string
 */
export const formatDateRange = (startDate: string, endDate: string): string => {
  const start = new Date(startDate);
  const end = new Date(endDate);

  // Format: March 15-17, 2025
  if (
    start.getMonth() === end.getMonth() &&
    start.getFullYear() === end.getFullYear()
  ) {
    return `${start.toLocaleString("default", { month: "long" })} ${start.getDate()}-${end.getDate()}, ${start.getFullYear()}`;
  }

  // Format: March 30 - April 2, 2025
  if (start.getFullYear() === end.getFullYear()) {
    return `${start.toLocaleString("default", { month: "long" })} ${start.getDate()} - ${end.toLocaleString("default", { month: "long" })} ${end.getDate()}, ${start.getFullYear()}`;
  }

  // Format: December 30, 2024 - January 2, 2025
  return `${start.toLocaleString("default", { month: "long" })} ${start.getDate()}, ${start.getFullYear()} - ${end.toLocaleString("default", { month: "long" })} ${end.getDate()}, ${end.getFullYear()}`;
};

/**
 * Formats a time range into a human-readable string with timezone
 * @param startDate ISO date string for the start date
 * @param endDate ISO date string for the end date
 * @returns Formatted time range string with timezone
 */
export const formatTimeRange = (startDate: string, endDate: string): string => {
  const start = new Date(startDate);
  const end = new Date(endDate);

  const startTime = start.toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
  const endTime = end.toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });

  return `${startTime} - ${endTime} EST`;
};
