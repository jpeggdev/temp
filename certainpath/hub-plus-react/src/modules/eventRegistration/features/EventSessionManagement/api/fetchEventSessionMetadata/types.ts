export interface TimezoneItem {
  id: number;
  name: string;
  identifier: string;
}

export interface GetCreateUpdateEventSessionMetadataResponse {
  data: {
    timezones: Array<TimezoneItem>;
  };
}
