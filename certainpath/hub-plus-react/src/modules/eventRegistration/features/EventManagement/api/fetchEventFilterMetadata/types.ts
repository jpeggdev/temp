export interface EventFilterMetadata {
  eventTypes: Array<{ id: number; name: string }>;
  eventCategories: Array<{ id: number; name: string }>;
  employeeRoles: Array<{ id: number; name: string }>;
  trades: Array<{ id: number; name: string }>;
  eventTags: Array<{ id: number; name: string }>;
}

export interface FetchEventFilterMetadataResponse {
  data: EventFilterMetadata;
}
