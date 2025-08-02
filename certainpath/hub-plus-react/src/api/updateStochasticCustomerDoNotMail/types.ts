export interface UpdateStochasticCustomerDoNotMailRequest {
  doNotMail: boolean;
}

export interface UpdateStochasticCustomerDoNotMailResponse {
  data: {
    data: {
      id: number;
      doNotMail: boolean;
    };
  };
}
