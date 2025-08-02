import React from "react";
import CertainPathTextInput from "../../../../../../components/CertainPathTextInput/CertainPathTextInput";
import CertainPathButton from "../../../../../../components/CertainPathButton/CertainPathButton";
import { CustomChangeEvent } from "../../../../../../hooks/useValidation";

interface FormValues {
  firstName: string;
  lastName: string;
}

interface EditUserFormProps {
  values: FormValues;
  errors: {
    firstName?: string | null;
    lastName?: string | null;
  };
  handleChange: (
    e: React.ChangeEvent<HTMLInputElement> | CustomChangeEvent,
  ) => void;
  handleSubmit: (e: React.FormEvent) => void;
  isFormValid: boolean;
}

const EditUserForm: React.FC<EditUserFormProps> = ({
  values,
  errors,
  handleChange,
  handleSubmit,
  isFormValid,
}) => {
  return (
    <form className="space-y-6" onSubmit={handleSubmit}>
      <CertainPathTextInput
        error={errors.firstName}
        name="firstName"
        onChange={handleChange}
        placeholder="First Name"
        value={values.firstName}
      />
      <CertainPathTextInput
        error={errors.lastName}
        name="lastName"
        onChange={handleChange}
        placeholder="Last Name"
        value={values.lastName}
      />
      <CertainPathButton disabled={!isFormValid} type="submit">
        Save
      </CertainPathButton>
    </form>
  );
};

export default EditUserForm;
