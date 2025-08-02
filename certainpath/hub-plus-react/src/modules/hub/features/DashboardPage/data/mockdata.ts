export const sampleData = [
  { name: "Jan", value: 400 },
  { name: "Feb", value: 300 },
  { name: "Mar", value: 600 },
  { name: "Apr", value: 800 },
  { name: "May", value: 500 },
];

export const initialLayouts = {
  lg: [
    { i: "line-1", x: 0, y: 0, w: 4, h: 6 },
    { i: "bar-1", x: 4, y: 0, w: 4, h: 6 },
    { i: "pie-1", x: 8, y: 0, w: 4, h: 6 },
  ],
};

export const initialCharts = [
  { id: "line-1", type: "line", data: sampleData },
  { id: "bar-1", type: "bar", data: sampleData },
  { id: "pie-1", type: "pie", data: sampleData },
];

export const SIZES = {
  FULL_ROW: { w: 12, h: 6 },
  HALF_ROW: { w: 8, h: 6 },
  THIRD_ROW: { w: 4, h: 6 },
};
