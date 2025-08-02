import { Location } from "@/modules/stochastic/features/LocationsList/api/createLocation/types";

export interface FetchLocationsRequest {
  page?: number;
  perPage?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  searchTerm?: string;
  isActive: number;
}

export interface FetchLocationsResponse {
  data: Location[];
  meta: {
    totalCount: number | null;
  };
}
