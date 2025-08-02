import { BlockType } from "@/modules/hub/features/ResourceManagement/components/SortableBlock/types";

export interface ResourceTag {
  id: number;
  name: string;
}

export interface ResourceCategory {
  id: number;
  name: string;
}

export interface ResourceTrade {
  id: number;
  name: string;
}

export interface ResourceRole {
  id: number;
  name: string;
}

export interface GetResourceContentBlock {
  id?: string;
  type: BlockType;
  content: string;
  order_number?: number;
  tmpFileId?: number;
  fileId?: number;
  fileUuid?: string;
  title?: string;
  shortDescription?: string;
}

export interface RelatedResourceItem {
  id: number;
  title: string;
}

export interface GetResourceResponse {
  data: {
    id: number | null;
    uuid: string | null;
    title: string | null;
    slug: string | null;
    description: string | null;
    tagline: string | null;
    contentUrl: string | null;
    thumbnailUrl: string | null;
    thumbnailFileId?: number;
    thumbnailFileUuid?: string;
    isPublished: boolean;
    publishStartDate: string | null;
    publishEndDate: string | null;
    typeId: number;
    icon: string;
    primaryIcon: string;
    backgroundColor: string;
    textColor: string;
    borderColor: string;
    tagIds: number[];
    tradeIds: number[];
    roleIds: number[];
    categoryIds: number[];
    contentBlocks: GetResourceContentBlock[];
    tags: ResourceTag[];
    categories: ResourceCategory[];
    typeName: string | null;
    viewCount: number;
    createdAt: string | null;
    updatedAt: string | null;
    trades: ResourceTrade[];
    roles: ResourceRole[];
    isFavorited: boolean;
    relatedResources: RelatedResourceItem[];
  };
}
