export interface EditResourceTagRequest {
  name: string;
}

export interface EditResourceTagResponse {
  data: {
    id: number | null;
    name: string | null;
  };
}
