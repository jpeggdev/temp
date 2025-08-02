import { Venue } from "@/modules/eventRegistration/features/EventVenueManagement/api/createVenue/types";

export interface FetchVenuesRequest {
  page?: number;
  pageSize?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  searchTerm?: string;
  isActive?: number;
}

export interface FetchVenuesResponse {
  data: Venue[];
  meta: {
    totalCount: number | null;
  };
}
