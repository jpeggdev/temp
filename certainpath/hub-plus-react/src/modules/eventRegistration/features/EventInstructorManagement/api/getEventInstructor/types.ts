export interface FetchedEventInstructorData {
  id: number;
  name: string;
  email: string;
  phone: string | null;
}

export interface GetEventInstructorResponse {
  data: FetchedEventInstructorData;
}
