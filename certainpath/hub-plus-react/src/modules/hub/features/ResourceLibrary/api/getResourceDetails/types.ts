import { BlockType } from "@/modules/hub/features/ResourceManagement/components/SortableBlock/types";

export interface GetResourceDetailsResponse {
  data: {
    id: number | null;
    uuid: string | null;
    title: string | null;
    slug: string | null;
    description: string | null;
    tagline: string | null;
    contentUrl: string | null;
    filename: string | null;
    thumbnailUrl: string | null;
    typeName: string | null;
    icon: string | null;
    primaryIcon: string | null;
    backgroundColor: string | null;
    textColor: string | null;
    borderColor: string | null;
    viewCount: number;
    publishStartDate: string | null;
    createdAt: string | null;
    updatedAt: string | null;

    categories: Array<{ id: number; name: string }>;
    trades: Array<{ id: number; name: string }>;
    roles: Array<{ id: number; name: string }>;
    tags: Array<{ id: number; name: string }>;
    contentBlocks: Array<{
      id: string;
      type: BlockType;
      content: string;
      order_number: number;
      fileId?: number;
      title?: string;
      shortDescription?: string;
    }>;

    isFavorited: boolean;

    relatedResources: Array<{
      title: string;
      slug: string;
      description: string;
      thumbnailUrl: string | null;
      primaryIcon: string | null;
      resourceType: string | null;
      createdOrPublishStartDate: string;
      viewCount: number;
      backgroundColor: string | null;
      textColor: string | null;
      borderColor: string | null;
    }>;
  };
}
