export interface GetEventCheckoutConfirmationDetailsResponseData {
  confirmationNumber: string | null;
  finalizedAt: string | null;
  amount: string | null;
  contactName: string | null;
  contactEmail: string | null;
  contactPhone: string | null;
  eventName: string | null;
  eventSessionName: string | null;
  startDate: string | null;
  endDate: string | null;
  isVirtualOnly: boolean;
  timezoneIdentifier: string | null;
  timezoneShortName: string | null;
  venueId: number | null;
  venueName: string | null;
  venueDescription: string | null;
  venueAddress: string | null;
  venueAddress2: string | null;
  venueCity: string | null;
  venueState: string | null;
  venuePostalCode: string | null;
  venueCountry: string | null;
}

export interface GetEventCheckoutConfirmationDetailsResponse {
  data: GetEventCheckoutConfirmationDetailsResponseData;
}
