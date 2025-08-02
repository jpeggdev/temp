const validationSchema = {
  firstName: [(value: string) => (value ? null : "First name is required")],
  lastName: [(value: string) => (value ? null : "Last name is required")],
  email: [
    (value: string) =>
      value.match(/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/)
        ? null
        : "Enter a valid email",
    (value: string) => (value ? null : "Email is required"),
  ],
};

export default validationSchema;
