export interface CreateVoucherRequest {
  name: string;
  description?: string | null;
  companyIdentifier: string;
  totalSeats: number;
  isActive: boolean;
  startDate?: string | null | undefined;
  endDate?: string | null | undefined;
}

export interface Company {
  id: number;
  name: string;
  companyIdentifier: string;
}

export interface Voucher {
  id: number;
  name: string;
  description?: string | null;
  companyName: string;
  companyIdentifier: string;
  totalSeats: number;
  availableSeats: number;
  usage: string;
  startDate: string;
  endDate: string;
  isActive: boolean;
  company: Company;
  createdAt?: string;
  updatedAt?: string;
}

export interface CreateVoucherResponse {
  data: Voucher;
}
