export interface SetPublishedResourceRequest {
  isPublished: boolean;
}

export interface SetPublishedResourceAPIResponse {
  data: {
    uuid: string;
    isPublished: boolean;
    title: string;
  };
}
