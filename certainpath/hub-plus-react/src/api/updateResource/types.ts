export interface UpdateResourceRequest {
  title: string;
  slug: string | null;
  tagline?: string | null;
  description: string;
  type: number;
  content_url?: string | null;
  thumbnail_url?: string | null;
  thumbnailTmpFileId?: number | null;
  thumbnailFileUuid?: string | null;
  publish_start_date?: string | null;
  publish_end_date?: string | null;
  is_published: boolean;
  tagIds?: number[];
  tradeIds?: string[];
  roleIds?: string[];
  categoryIds?: number[];
  relatedResourceIds?: number[];
  contentBlocks?: UpdateResourceContentBlock[];
}

export interface UpdateResourceContentBlock {
  id?: string | null;
  type: string;
  content: string;
  order_number: number;
  tmpFileId?: number | null | undefined;
  fileId?: number | null | undefined;
  fileUuid?: string | null;
  title?: string | null;
  shortDescription?: string | null;
}

export interface UpdateResourceResponse {
  data: {
    id: number | null;
    uuid: string | null;
    title: string | null;
    contentUrl: string | null;
    thumbnailUrl: string | null;
    isPublished: boolean;
  };
}
