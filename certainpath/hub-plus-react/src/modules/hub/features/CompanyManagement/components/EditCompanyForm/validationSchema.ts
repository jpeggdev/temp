const validationSchema = {
  companyName: [
    (value: string) => (value ? null : "Company name is required"),
    (value: string) =>
      value.length <= 255
        ? null
        : "Company name must be 255 characters or less",
  ],
  salesforceId: [
    (value: string | null | undefined) =>
      !value || value.length <= 255
        ? null
        : "Salesforce ID must be 255 characters or less",
  ],
  intacctId: [
    (value: string | null | undefined) =>
      !value || value.length <= 255
        ? null
        : "Intacct ID must be 255 characters or less",
  ],
  companyEmail: [
    (value: string | null | undefined) =>
      !value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
        ? null
        : "Enter a valid email address",
    (value: string | null | undefined) =>
      value ? null : "Company email is required",
  ],
  websiteUrl: [
    (value: string | null | undefined) => {
      if (!value || !value.trim()) {
        return null;
      }
      const urlPattern = /^(https?:\/\/)[^\s/$.?#].[^\s]*$/;
      return urlPattern.test(value)
        ? null
        : "Enter a valid URL starting with http:// or https://";
    },
  ],
  marketingEnabled: [() => null],
};

export default validationSchema;
