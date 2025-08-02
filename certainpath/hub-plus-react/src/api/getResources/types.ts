export interface GetResourcesItem {
  id: number;
  uuid: string;
  title: string;
  description: string;
  isPublished: boolean;
  thumbnailUrl?: string | null;
  primaryIcon: string;
  isFeatured: boolean;
  resourceType?: string | null;
  createdAt: string;
}

export interface GetResourcesAPIResponse {
  data: GetResourcesItem[];
  meta?: {
    totalCount?: number;
  };
}

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
}
