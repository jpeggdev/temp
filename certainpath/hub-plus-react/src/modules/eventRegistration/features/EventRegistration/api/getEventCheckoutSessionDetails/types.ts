export interface EventCheckoutSessionAttendee {
  id: number;
  email: string;
  firstName: string;
  lastName: string;
  specialRequests: string | null;
  isSelected: boolean;
  isWaitlist: boolean;
}

export interface EventCheckoutSessionDiscount {
  id: number;
  code: string | null;
  discountType: string | null;
  discountValue: string | null;
}

export interface EventCheckoutSessionVenue {
  id: number | null;
  name: string | null;
  description: string | null;
  address: string | null;
  address2: string | null;
  city: string | null;
  state: string | null;
  postalCode: string | null;
  country: string | null;
}

export interface GetEventCheckoutSessionDetailsResponseData {
  id: number | null;
  eventUuid: string | null;
  eventSessionUuid: string | null;
  uuid: string | null;
  status: string | null;
  reservationExpiresAt: string | null;
  createdById: number;
  eventSessionId: number | null;
  contactName: string | null;
  contactEmail: string | null;
  contactPhone: string | null;
  groupNotes: string | null;
  createdAt: string | null;
  updatedAt: string | null;
  attendees: EventCheckoutSessionAttendee[];
  eventName: string | null;
  eventPrice: number | null;
  eventSessionName: string | null;
  maxEnrollments: number | null;
  availableSeats: number | null;
  notes: string | null;
  startDate: string | null;
  endDate: string | null;
  companyAvailableVoucherSeats: number;
  discounts: EventCheckoutSessionDiscount[];
  venue: EventCheckoutSessionVenue | null;
  timezoneIdentifier: string | null;
  timezoneShortName: string | null;
  isVirtualOnly: boolean;
  occupiedAttendeeSeatsByCurrentUser: number;
}

export interface GetEventCheckoutSessionDetailsResponse {
  data: GetEventCheckoutSessionDetailsResponseData;
}
