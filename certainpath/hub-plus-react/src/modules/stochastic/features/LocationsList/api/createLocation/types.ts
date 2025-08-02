export interface Location {
  id: number;
  name: string;
  description?: string | null;
  postalCodes: string[];
  isActive: boolean;
}

export interface CreateLocationRequest {
  name: string;
  description?: string | null;
  postalCodes: string[];
}

export interface CreateLocationResponse {
  data: Location;
}
