import React from "react";
import Cleave from "cleave.js/react";
import { Props as CleaveProps } from "cleave.js/react/props";

export type CleaveChangeEvent = React.ChangeEvent<HTMLInputElement> & {
  target: React.ChangeEvent<HTMLInputElement>["target"] & {
    rawValue: string;
  };
};

export interface CustomCleaveInputProps
  extends Omit<CleaveProps, "onChange" | "value"> {
  value?: string;
  onChange?: (rawValue: string, event: CleaveChangeEvent) => void;
}

function CustomCleaveInput({
  value = "",
  onChange,
  options,
  ...rest
}: CustomCleaveInputProps) {
  const defaultOptions = {
    numeral: true,
    numeralDecimalScale: 2,
    numeralThousandsGroupStyle: "thousand",
    prefix: "$",
    rawValueTrimPrefix: true,
    numeralPositiveOnly: true,
    ...options,
  } as const;

  return (
    <Cleave
      {...rest}
      onChange={(e: CleaveChangeEvent) => {
        const rawVal = e.target.rawValue;
        onChange?.(rawVal, e);
      }}
      options={defaultOptions}
      value={value}
    />
  );
}

export default CustomCleaveInput;
