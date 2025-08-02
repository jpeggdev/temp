export const chartData = {
  area: [
    { days: "Jan", salesPercentage: 40, totalSales: 500000 },
    { days: "Feb", salesPercentage: 45, totalSales: 700000 },
    { days: "Mar", salesPercentage: 50, totalSales: 900000 },
    { days: "Apr", salesPercentage: 55, totalSales: 1100000 },
  ],
  bar: [
    { days: "Q1", salesPercentage: 45, totalSales: 1500000 },
    { days: "Q2", salesPercentage: 55, totalSales: 2100000 },
    { days: "Q3", salesPercentage: 48, totalSales: 1800000 },
    { days: "Q4", salesPercentage: 62, totalSales: 2400000 },
  ],
  line: [
    { days: "0-30 days", salesPercentage: 65, totalSales: 2994999 },
    { days: "31-60 days", salesPercentage: 20, totalSales: 133680 },
    { days: "61-90 days", salesPercentage: 8, totalSales: 62176 },
    { days: "91-365 days", salesPercentage: 5, totalSales: 35149 },
    { days: "Year 2+", salesPercentage: 2, totalSales: 28545 },
  ],
  pie: [
    { days: "Product A", salesPercentage: 30, totalSales: 800000 },
    { days: "Product B", salesPercentage: 25, totalSales: 600000 },
    { days: "Product C", salesPercentage: 20, totalSales: 400000 },
    { days: "Others", salesPercentage: 25, totalSales: 200000 },
  ],
};

export const filterOptions = {
  cities: ["New York", "London", "Tokyo", "Paris", "Singapore"],
  trades: ["Forex", "Stocks", "Crypto", "Commodities", "Bonds"],
  years: ["2020", "2021", "2022", "2023", "2024"],
};

export const yearlyComparisonData = {
  months: [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ],
  years: {
    "2018": [
      75000, 45000, 130000, 80000, 150000, 170000, 210000, 170000, 185000,
      160000, 130000, 130000,
    ],
    "2019": [
      155000, 70000, 190000, 65000, 270000, 265000, 315000, 170000, 100000,
      95000, 55000, 55000,
    ],
    "2020": [
      85000, 65000, 135000, 35000, 140000, 175000, 280000, 100000, 120000,
      110000, 85000, 45000,
    ],
    "2021": [
      60000, 45000, 140000, 170000, 220000, 180000, 170000, 120000, 130000,
      190000, 110000, 125000,
    ],
    "2022": [
      90000, 85000, 185000, 360000, 230000, 320000, 140000, 425000, 180000,
      200000, 155000, 150000,
    ],
    "2023": [
      150000, 95000, 175000, 220000, 225000, 280000, 320000, 200000, 275000,
      100000, 140000, 135000,
    ],
  },
};

interface MonthData {
  month: string;
  [year: string]: string | number;
}

export const getYearlyChartData = () => {
  return yearlyComparisonData.months.map((month, index) => {
    const monthData: MonthData = { month };
    Object.entries(yearlyComparisonData.years).forEach(([year, values]) => {
      monthData[year] = values[index];
    });
    return monthData;
  });
};

export const customerSalesData = [
  { HF: 300000, NC: 1377573, total: 1677573, year: "2018" },
  { HF: 400000, NC: 1688986, total: 2088986, year: "2019" },
  { HF: 243376, NC: 1100000, total: 1343376, year: "2020" },
  { HF: 384459, NC: 1100000, total: 1484459, year: "2021" },
  { HF: 504266, NC: 1900000, total: 2404266, year: "2022" },
  { HF: 597579, NC: 1900000, total: 2497579, year: "2023" },
];

export const topZipsData = [
  { cumulativePercentage: 3, totalSales: 1245000, zipCode: "90210" },
  { cumulativePercentage: 4, totalSales: 980500, zipCode: "10001" },
  { cumulativePercentage: 5, totalSales: 875200, zipCode: "60601" },
  { cumulativePercentage: 8, totalSales: 750300, zipCode: "02108" },
  { cumulativePercentage: 12, totalSales: 680100, zipCode: "33139" },
  { cumulativePercentage: 18, totalSales: 620800, zipCode: "94102" },
  { cumulativePercentage: 22, totalSales: 590400, zipCode: "98101" },
  { cumulativePercentage: 26, totalSales: 550200, zipCode: "20001" },
  { cumulativePercentage: 32, totalSales: 510900, zipCode: "77002" },
  { cumulativePercentage: 40, totalSales: 480600, zipCode: "30303" },
];

export const zipData = [
  {
    zipCode: "68025",
    "2018": 450000,
    "2019": 380000,
    "2020": 420000,
    "2021": 390000,
    "2022": 410000,
    "2023": 480000,
  },
  {
    zipCode: "68064",
    "2018": 280000,
    "2019": 250000,
    "2020": 310000,
    "2021": 290000,
    "2022": 300000,
    "2023": 320000,
  },
  {
    zipCode: "68025",
    "2018": 180000,
    "2019": 220000,
    "2020": 230000,
    "2021": 250000,
    "2022": 260000,
    "2023": 270000,
  },
];
