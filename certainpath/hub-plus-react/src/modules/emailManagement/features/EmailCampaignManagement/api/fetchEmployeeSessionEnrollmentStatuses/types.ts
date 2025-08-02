export interface FetchEmployeeSessionEnrollmentStatusesRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC";
  isActive?: boolean;
}

export interface ApiEmployeeSessionEnrollmentStatus {
  id: number;
  name: string;
  displayName: string;
  description: string;
  isActive: boolean;
  displayOrder: number;
}

export interface FetchEmployeeSessionEnrollmentStatusesResponse {
  data: ApiEmployeeSessionEnrollmentStatus[];
  meta?: {
    totalCount?: number;
  };
}
