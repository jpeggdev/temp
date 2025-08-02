import { Venue } from "@/modules/eventRegistration/features/EventVenueManagement/api/createVenue/types";

export interface EditVenueRequest {
  name: string;
  description?: string | null;
  address: string;
  address2?: string | null;
  city: string;
  state: string;
  postalCode: string;
  country: string;
  isActive?: boolean | null;
}

export interface EditVenueResponse {
  data: Venue;
}
