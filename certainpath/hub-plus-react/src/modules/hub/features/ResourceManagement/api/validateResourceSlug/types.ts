export interface ValidateResourceSlugRequest {
  slug: string;
  resourceUuid?: string | null;
}

export interface ValidateResourceSlugResponse {
  data: {
    slugExists: boolean;
    message?: string | null;
  };
}
