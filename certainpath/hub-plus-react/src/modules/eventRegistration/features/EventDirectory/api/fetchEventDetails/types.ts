export interface FetchEventDetailsRequest {
  uuid: string;
}

export interface FileType {
  id: number;
  uuid: string;
  originalFileName: string;
  fileUrl: string | null;
}

export interface VenueType {
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

export interface SessionData {
  id: number;
  uuid: string;
  name: string;
  isPublished: boolean;
  startDate: string;
  endDate: string;
  maxEnrollments: number;
  availableSeats: number;
  virtualLink: string | null;
  notes: string | null;
  isVirtualOnly: boolean;
  timezoneShortName: string | null;
  timezoneIdentifier: string | null;
  venue: VenueType | null;
  occupiedAttendeeSeatsByCurrentUser: number;
  timeLeftForCurrentUser: number | null;
}

export interface EventDetails {
  id: number | null;
  uuid: string | null;
  eventCode: string;
  eventName: string;
  eventDescription: string;
  eventPrice: number;
  isPublished: boolean;
  eventTypeName: string | null;
  eventCategoryName: string | null;
  thumbnailUrl: string | null;
  viewCount: number;
  createdAt: string | null;
  updatedAt: string | null;
  tags: Array<{ id: number; name: string }>;
  trades: Array<{ id: number; name: string }>;
  roles: Array<{ id: number; name: string }>;
  sessions: SessionData[];
  files: FileType[];

  isFavorited: boolean;
  isVoucherEligible: boolean;
}

export interface FetchEventDetailsResponse {
  data: EventDetails;
}
