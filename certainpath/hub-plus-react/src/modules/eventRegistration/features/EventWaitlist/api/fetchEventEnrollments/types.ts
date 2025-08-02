export interface FetchEventEnrollmentsRequest {
  uuid: string;
  searchTerm?: string;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  page?: number;
  pageSize?: number;
}

export interface ReplacementType {
  employeeId: number;
  firstName: string;
  lastName: string;
  workEmail: string;
}

export interface EventEnrollmentItemResponseDTO {
  id: number;
  firstName: string | null;
  lastName: string | null;
  email: string | null;
  companyName: string | null;
  companyId: number | null;
  enrolledAt: string | null;
  replacements: ReplacementType[];
}

export interface FetchEventEnrollmentsResponse {
  data: EventEnrollmentItemResponseDTO[];
  meta?: {
    totalCount: number;
  };
}
