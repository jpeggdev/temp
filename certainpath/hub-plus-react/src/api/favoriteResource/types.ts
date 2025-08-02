export interface FavoriteResourceRequestParams {
  resourceUuid: string;
}

export interface FavoriteResourceResponse {
  data: {
    favorited: boolean;
  };
}
