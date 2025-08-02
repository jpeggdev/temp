export interface SearchEventInstructorsRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name" | "email" | "phone";
  sortOrder?: "ASC" | "DESC";
}

export interface SearchEventInstructorItem {
  id: number;
  name: string;
  email: string;
  phone: string | null;
}

export interface SearchEventInstructorsData {
  instructors: SearchEventInstructorItem[];
  totalCount: number;
}

export interface SearchEventInstructorsResponse {
  data: SearchEventInstructorsData;
  meta?: {
    totalCount: number;
  };
}
