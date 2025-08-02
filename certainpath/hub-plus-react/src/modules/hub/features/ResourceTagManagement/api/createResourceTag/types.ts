export interface CreateResourceTagRequest {
  name: string;
}

export interface CreateResourceTagResponse {
  data: {
    id: number | null;
    name: string | null;
  };
}
