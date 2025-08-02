interface CoachMetrics {
  companyName: string;
  contactName: string;
  location: string;
  phone: string;
  email: string;
  memberSince: string;
  metrics: {
    clubMemberConversion: { value: number; trend: "up" | "down" };
    grossMargin: { value: number; trend: "up" | "down" };
    revenuePerEmployee: { value: number; trend: "up" | "down" };
    csrBookedRatio: { value: number; trend: "up" | "down" };
    technicianClosingRatio: { value: number; trend: "up" | "down" };
    averageInvoice: { value: number; trend: "up" | "down" };
    callCenterScore: { value: number; trend: "up" | "down" };
    replacementClosingRate: { value: number; trend: "up" | "down" };
  };
}

export const coachingDashboardMockData: CoachMetrics[] = [
  {
    companyName: "Advanced Circuit",
    contactName: "Charles Fraser",
    location: "Las Vegas, NV 89101",
    phone: "(702) 555-0123",
    email: "charles.fraser@advancedcircuit.com",
    memberSince: "2019",
    metrics: {
      clubMemberConversion: { value: 27, trend: "down" },
      grossMargin: { value: 62, trend: "up" },
      revenuePerEmployee: { value: 923, trend: "up" },
      csrBookedRatio: { value: 78, trend: "up" },
      technicianClosingRatio: { value: 74, trend: "up" },
      averageInvoice: { value: 480, trend: "up" },
      callCenterScore: { value: 4.2, trend: "down" },
      replacementClosingRate: { value: 55, trend: "down" },
    },
  },
  {
    companyName: "Blue Flame HVAC",
    contactName: "Michael Andrews",
    location: "Santa Ana, CA",
    phone: "(714) 555-4567",
    email: "william.hensley@blueflamehvac.com",
    memberSince: "2020",
    metrics: {
      clubMemberConversion: { value: 32, trend: "down" },
      grossMargin: { value: 67, trend: "up" },
      revenuePerEmployee: { value: 1223, trend: "up" },
      csrBookedRatio: { value: 82, trend: "up" },
      technicianClosingRatio: { value: 78, trend: "up" },
      averageInvoice: { value: 510, trend: "up" },
      callCenterScore: { value: 4.8, trend: "down" },
      replacementClosingRate: { value: 75, trend: "up" },
    },
  },
  {
    companyName: "Circuitry Masters",
    contactName: "Robert McElroy",
    location: "Los Angeles, CA",
    phone: "(213) 555-8910",
    email: "michael.andrews@circuitrymasters.com",
    memberSince: "2017",
    metrics: {
      clubMemberConversion: { value: 29, trend: "down" },
      grossMargin: { value: 36, trend: "down" },
      revenuePerEmployee: { value: 1119, trend: "up" },
      csrBookedRatio: { value: 62, trend: "up" },
      technicianClosingRatio: { value: 75, trend: "up" },
      averageInvoice: { value: 350, trend: "up" },
      callCenterScore: { value: 3.8, trend: "down" },
      replacementClosingRate: { value: 58, trend: "up" },
    },
  },
  {
    companyName: "Elite HVAC Solutions",
    contactName: "Sarah Martinez",
    location: "Phoenix, AZ",
    phone: "(602) 555-3344",
    email: "sarah.martinez@elitehvac.com",
    memberSince: "2018",
    metrics: {
      clubMemberConversion: { value: 45, trend: "up" },
      grossMargin: { value: 58, trend: "up" },
      revenuePerEmployee: { value: 1450, trend: "up" },
      csrBookedRatio: { value: 85, trend: "up" },
      technicianClosingRatio: { value: 82, trend: "up" },
      averageInvoice: { value: 595, trend: "up" },
      callCenterScore: { value: 4.6, trend: "up" },
      replacementClosingRate: { value: 72, trend: "up" },
    },
  },
];

export type { CoachMetrics };
