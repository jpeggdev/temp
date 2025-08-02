import { useState, useMemo } from "react";

type ValidationRule<V> = (value: V) => string | null;

type ValidationSchema<T> = {
  [K in keyof T]: ValidationRule<T[K]>[];
};

export type CustomChangeEvent = {
  target: {
    name: string;
    value: string;
  };
};

export function useValidation<T>(
  initialValues: T,
  validationSchema: ValidationSchema<T>,
) {
  const [values, setValues] = useState<T>(initialValues);
  const [errors, setErrors] = useState<{ [K in keyof T]?: string | null }>({});

  const validateField = <K extends keyof T>(name: K, value: T[K]) => {
    const rules = validationSchema[name];
    if (!rules) return null;

    for (const rule of rules) {
      const error = rule(value);
      if (error) return error;
    }

    return null;
  };

  const validateForm = () => {
    const newErrors: { [K in keyof T]?: string | null } = {};

    (Object.keys(validationSchema) as Array<keyof T>).forEach((key) => {
      const error = validateField(key, values[key]);
      newErrors[key] = error;
    });

    setErrors(newErrors);

    return Object.values(newErrors).every(
      (error): error is null => error === null,
    );
  };

  const isFormValid = useMemo(() => {
    return Object.values(errors).every(
      (error) => error === null || error === undefined,
    );
  }, [errors]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement> | CustomChangeEvent,
  ) => {
    let name: keyof T;
    let newValue: string | boolean;

    if ("target" in e && "type" in e.target) {
      const { target } = e as React.ChangeEvent<HTMLInputElement>;
      name = target.name as keyof T;
      newValue = target.type === "checkbox" ? target.checked : target.value;
    } else {
      name = e.target.name as keyof T;
      newValue = e.target.value;
    }

    setValues({ ...values, [name]: newValue as T[keyof T] });

    if (validationSchema[name]) {
      const error = validateField(name, newValue as T[keyof T]);
      setErrors({ ...errors, [name]: error });
    }
  };

  return {
    values,
    setValues,
    errors,
    handleChange,
    validateForm,
    isFormValid,
  };
}
