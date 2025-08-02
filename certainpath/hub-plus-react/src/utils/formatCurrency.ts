const usdFormatter = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "USD",
});

export function formatCurrency(
  value: string | number | null | undefined,
): string {
  if (value == null) {
    return "N/A";
  }
  const numericVal = typeof value === "string" ? parseFloat(value) : value;
  if (Number.isNaN(numericVal)) {
    return "N/A";
  }
  return usdFormatter.format(numericVal);
}
