export interface EditUserRequest {
  firstName: string;
  lastName: string;
}

export interface EditUserResponse {
  data: {
    id: number;
    firstName: string;
    lastName: string;
    email: string;
    salesforceId?: string | null;
    uuid: string;
  };
}
