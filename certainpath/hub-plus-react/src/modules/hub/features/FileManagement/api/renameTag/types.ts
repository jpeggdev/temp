export interface RenameTagRequest {
  name: string;
  color?: string | null;
}

export interface RenameTagData {
  id: number;
  oldName: string;
  newName: string;
  color: string | null;
  updatedAt: string;
}

export interface RenameTagResponse {
  data: RenameTagData;
}
