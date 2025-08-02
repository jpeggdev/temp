export interface FetchUsersRequest {
  firstName?: string;
  lastName?: string;
  email?: string;
  searchTerm?: string;
  salesforceId?: string;
  page?: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC";
  pageSize?: number;
}

export interface FetchUsersResponse {
  data: {
    users: User[];
  };
  meta?: {
    totalCount: number;
  };
}

export interface User {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  uuid: string;
  employeeUuid: string;
  salesforceId?: string | null;
}
