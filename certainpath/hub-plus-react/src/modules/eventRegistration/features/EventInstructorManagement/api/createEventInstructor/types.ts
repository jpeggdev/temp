export interface CreateEventInstructorRequest {
  name: string;
  email: string;
  phone?: string | null;
}

export interface CreatedEventInstructorData {
  id: number;
  name: string;
  email: string;
  phone: string | null;
}

export interface CreateEventInstructorResponse {
  data: CreatedEventInstructorData;
}
