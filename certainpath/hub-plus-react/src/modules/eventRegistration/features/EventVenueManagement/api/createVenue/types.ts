export interface CreateVenueRequest {
  name: string;
  description?: string | null;
  address: string;
  address2?: string | null;
  city: string;
  state: string;
  postalCode: string;
  country: string;
}

export interface Venue {
  id: number;
  name: string;
  description?: string | null;
  address: string;
  address2?: string | null;
  city: string;
  state: string;
  postalCode: string;
  country: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt?: string | null;
}

export interface CreateVenueResponse {
  data: Venue;
}
