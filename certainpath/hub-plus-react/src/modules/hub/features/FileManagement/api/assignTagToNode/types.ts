export interface AssignTagToNodeRequest {
  tagId: number;
  filesystemNodeUuid: string;
}

export interface AssignTagToNodeData {
  mappingId: number;
  tagId: number;
  tagName: string;
  tagColor: string | null;
  filesystemNodeUuid: string;
  filesystemNodeName: string;
}

export interface AssignTagToNodeResponse {
  data: AssignTagToNodeData;
}
