export interface GetResourceSearchResultsItem {
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

export interface ResourceTypeFacet {
  id: number;
  name: string;
  icon: string;
  resourceCount: number;
}

export interface GetResourceSearchResultsAPIResponse {
  data: {
    resources: GetResourceSearchResultsItem[];
    filters?: {
      resourceTypes?: ResourceTypeFacet[];
    };
  };
  meta?: {
    totalCount?: number;
  };
}

export interface GetResourceSearchResultsRequest {
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
