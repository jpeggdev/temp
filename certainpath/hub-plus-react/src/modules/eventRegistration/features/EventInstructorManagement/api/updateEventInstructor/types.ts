export interface UpdateEventInstructorRequest {
  name: string;
  email: string;
  phone?: string | null;
}

export interface UpdatedEventInstructorData {
  id: number;
  name: string;
  email: string;
  phone: string | null;
}

export interface UpdateEventInstructorResponse {
  data: UpdatedEventInstructorData;
}
