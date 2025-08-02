export interface CreateAndAssignTagRequest {
  name: string;
  color?: string | null;
  filesystemNodeUuid: string;
}

export interface CreateAndAssignTagData {
  id: number;
  name: string;
  color: string | null;
  mappingId: number;
  filesystemNodeUuid: string;
  createdAt: string;
  updatedAt: string;
}

export interface CreateAndAssignTagResponse {
  data: CreateAndAssignTagData;
}
