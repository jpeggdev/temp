const validationSchema = {
  firstName: [(value: string) => (value ? null : "First name is required")],
  lastName: [(value: string) => (value ? null : "Last name is required")],
};

export default validationSchema;
