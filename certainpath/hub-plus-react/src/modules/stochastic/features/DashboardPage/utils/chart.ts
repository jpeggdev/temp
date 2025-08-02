export const generateColors = (years: string[], theme: string) => {
  const saturation = theme === "light" ? 70 : 80;
  const lightness = 60;
  const primeStep = 137;
  return years.reduce(
    (acc, year, i) => {
      acc[year] =
        `hsl(${(i * primeStep) % 360}, ${saturation}%, ${lightness}%)`;
      return acc;
    },
    {} as Record<string, string>,
  );
};

export const formatCurrency = (value: number): string =>
  new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    maximumFractionDigits: 0,
  }).format(value);

export const formatPercentage = (value: number) => {
  return `${value}%`;
};
