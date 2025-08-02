export interface CreateUserRequest {
  firstName: string;
  lastName: string;
  email: string;
}

export interface CreateUserResponse {
  data: {
    id: number;
    firstName: string;
    lastName: string;
    email: string;
    employeeUuid: string;
    salesforceId?: string | null;
  };
}
