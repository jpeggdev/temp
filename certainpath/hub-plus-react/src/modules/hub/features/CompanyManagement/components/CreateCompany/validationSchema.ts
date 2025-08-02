const validationSchema = {
  companyName: [(value: string) => (value ? null : "Company name is required")],
  websiteUrl: [
    (value: string) => {
      if (!value || value.trim() === "") {
        return null;
      }

      return /^https?:\/\/([\da-z.-]+)\.([a-z.]{2,6})(\/.*)?$/.test(value)
        ? null
        : "Enter a valid website URL starting with http:// or https://";
    },
  ],
  companyEmail: [
    (value: string) =>
      /^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/.test(value)
        ? null
        : "Enter a valid email",
    (value: string) => (value ? null : "Company email is required"),
  ],
  salesforceId: [() => null],
  intacctId: [() => null],
};

export default validationSchema;
