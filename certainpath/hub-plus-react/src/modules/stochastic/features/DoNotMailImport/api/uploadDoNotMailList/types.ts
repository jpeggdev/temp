export interface uploadDoNotMailRequest {
  file: File;
}

export interface Address {
  address1: string;
  address2: string;
  city: string;
  state: string;
  zip: string;
  isMatched: boolean;
  externalId: string;
}

export interface uploadDoNotMailResponse {
  data: {
    addresses: Address[];
    matchesCount: number;
  };
}
