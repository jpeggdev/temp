export interface GetResourcesRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: string;
  tradeIds?: number[];
  categoryIds?: number[];
  employeeRoleIds?: number[];
  tagIds?: number[];
  resourceTypeIds?: number[];
  showFavorites?: boolean;
}

export interface Resource {
  id: number;
  uuid: string;
  title: string;
  slug: string;
  description: string;
  isPublished: boolean;
  thumbnailUrl: string | null;
  primaryIcon: string;
  isFeatured: boolean;
  resourceType: string | null;
  createdOrPublishStartDate: string | null;
  viewCount: number;
  backgroundColor: string | null;
  textColor: string | null;
  borderColor: string | null;
}

export interface GetResourcesResponse {
  data: Resource[];
  meta?: {
    totalCount?: number;
  };
}
