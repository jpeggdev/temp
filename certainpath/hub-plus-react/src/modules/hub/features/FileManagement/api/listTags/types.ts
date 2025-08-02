export interface TagSummary {
  id: number;
  name: string;
  color: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface ListTagsData {
  tags: TagSummary[];
  totalCount: number;
}

export interface ListTagsResponse {
  data: ListTagsData;
}
