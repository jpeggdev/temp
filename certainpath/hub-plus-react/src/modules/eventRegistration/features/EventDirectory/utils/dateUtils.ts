export const knownShortTimeZones: Record<string, string> = {
  "Eastern Daylight Time": "EDT",
  "Eastern Standard Time": "EST",
  "Central Daylight Time": "CDT",
  "Central Standard Time": "CST",
  "Mountain Daylight Time": "MDT",
  "Mountain Standard Time": "MST",
  "Pacific Daylight Time": "PDT",
  "Pacific Standard Time": "PST",
  "Alaska Daylight Time": "AKDT",
  "Alaska Standard Time": "AKST",
  "Hawaiian Standard Time": "HST",
};

export function formatDateTimeInTimeZone(
  dateString: string | null,
  timeZone: string | null,
): string {
  if (!dateString) return "--";
  if (!timeZone) {
    return new Date(dateString).toLocaleString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "numeric",
      minute: "2-digit",
    });
  }
  return new Intl.DateTimeFormat("en-US", {
    timeZone,
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  }).format(new Date(dateString));
}

export function formatDateTimeRangeInTimeZone(
  startDate: string | null,
  endDate: string | null,
  timeZone: string | null,
): string {
  if (!startDate) return "--";
  const startStr = formatDateTimeInTimeZone(startDate, timeZone);
  if (!endDate) return startStr;
  const endStr = formatDateTimeInTimeZone(endDate, timeZone);
  return `${startStr} - ${endStr}`;
}

export function formatLocalDateTime(dateString: string | null): string {
  if (!dateString) return "--";
  return new Date(dateString).toLocaleString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
}

export function formatLocalDateTimeRange(
  startDate: string | null,
  endDate: string | null,
): string {
  if (!startDate) return "--";
  const startStr = formatLocalDateTime(startDate);
  if (!endDate) return startStr;
  const endStr = formatLocalDateTime(endDate);
  return `${startStr} - ${endStr}`;
}

export function formatDateInTimeZone(
  dateString: string | null,
  timeZone: string | null,
): string {
  if (!dateString) return "--";
  if (!timeZone) {
    return new Date(dateString).toLocaleDateString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  }
  return new Intl.DateTimeFormat("en-US", {
    timeZone,
    year: "numeric",
    month: "short",
    day: "numeric",
  }).format(new Date(dateString));
}

export function formatDateRangeInTimeZone(
  startDate: string | null,
  endDate: string | null,
  timeZone: string | null,
): string {
  if (!startDate) return "--";
  const formattedStart = formatDateInTimeZone(startDate, timeZone);
  if (!endDate) return formattedStart;
  const formattedEnd = formatDateInTimeZone(endDate, timeZone);
  return formattedStart === formattedEnd
    ? formattedStart
    : `${formattedStart} - ${formattedEnd}`;
}

export function formatTimeRangeInTimeZone(
  startDate: string | null,
  endDate: string | null,
  timeZone: string | null,
): string {
  if (!startDate) return "--";
  const startObj = new Date(startDate);
  const endObj = endDate ? new Date(endDate) : null;

  const options: Intl.DateTimeFormatOptions = {
    timeZone: timeZone || undefined,
    hour: "numeric",
    minute: "2-digit",
  };

  const startTime = new Intl.DateTimeFormat("en-US", options).format(startObj);
  if (!endObj) return startTime;
  const endTime = new Intl.DateTimeFormat("en-US", options).format(endObj);
  return `${startTime} - ${endTime}`;
}

export function formatLocalTimeRange(
  startDate: string | null,
  endDate: string | null,
): string {
  if (!startDate) return "--";
  const startObj = new Date(startDate);
  const endObj = endDate ? new Date(endDate) : null;

  const options: Intl.DateTimeFormatOptions = {
    hour: "numeric",
    minute: "2-digit",
  };

  const startTime = startObj.toLocaleTimeString([], options);
  if (!endObj) return startTime;
  const endTime = endObj.toLocaleTimeString([], options);
  return `${startTime} - ${endTime}`;
}

export function getLocalTimeZoneShortName(): string {
  const match = new Date().toTimeString().match(/\(([^)]+)\)/);
  if (match) {
    const potentialLongName = match[1];
    return knownShortTimeZones[potentialLongName] || potentialLongName;
  }
  return "Local";
}
