import React from "react";
import { useDispatch, useSelector } from "react-redux";
import { createUserAction } from "../../slices/usersSlice";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { RootState } from "../../../../../../app/rootReducer";
import { useValidation } from "../../../../../../hooks/useValidation";
import CertainPathTextInput from "../../../../../../components/CertainPathTextInput/CertainPathTextInput";
import CertainPathButton from "../../../../../../components/CertainPathButton/CertainPathButton";
import { useNotification } from "../../../../../../context/NotificationContext";
import validationSchema from "./validationSchema";
import { useNavigate } from "react-router-dom";

const CreateUser: React.FC = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { loading, error } = useSelector((state: RootState) => state.users);
  const { showNotification } = useNotification();

  const { values, errors, handleChange, validateForm } = useValidation(
    { firstName: "", lastName: "", email: "" },
    validationSchema,
  );

  const isFormValid = () =>
    !Object.values(errors).some((error) => error !== null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm()) {
      dispatch(
        createUserAction(
          {
            firstName: values.firstName,
            lastName: values.lastName,
            email: values.email,
          },
          (newUser) => {
            showNotification(
              "Successfully created user!",
              "The user information has been created.",
              "success",
            );
            navigate(`/hub/users/${newUser.employeeUuid}/edit`);
          },
        ),
      );
    }
  };

  return (
    <MainPageWrapper error={error} loading={loading} title="Create User">
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
        <CertainPathTextInput
          error={errors.email}
          name="email"
          onChange={handleChange}
          placeholder="Email"
          value={values.email}
        />
        <CertainPathButton disabled={!isFormValid()} type="submit">
          Create
        </CertainPathButton>
      </form>
    </MainPageWrapper>
  );
};

export default CreateUser;
