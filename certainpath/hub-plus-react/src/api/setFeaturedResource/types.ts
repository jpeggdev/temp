export interface SetFeaturedResourceRequest {
  isFeatured: boolean;
}

export interface SetFeaturedResourceAPIResponse {
  data: {
    uuid: string;
    isFeatured: boolean;
    title: string;
  };
}
