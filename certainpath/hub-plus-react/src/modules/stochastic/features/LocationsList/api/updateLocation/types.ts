import { Location } from "@/modules/stochastic/features/LocationsList/api/createLocation/types";

export interface UpdateLocationRequest {
  name: string;
  description?: string | null;
  postalCodes: string[];
}

export interface UpdateLocationResponse {
  data: Location;
}
