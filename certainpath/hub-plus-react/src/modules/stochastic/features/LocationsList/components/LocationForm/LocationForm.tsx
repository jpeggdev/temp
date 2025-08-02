import React, { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/utils/utils";
import { Badge } from "@/components/ui/badge";
import { X } from "lucide-react";

export interface LocationFormValues {
  name: string;
  description?: string | null;
  postalCodes: string[];
}

interface LocationFormProps {
  initialData: LocationFormValues;
  loading?: boolean;
  onSubmit: (values: LocationFormValues) => void;
}

const LocationForm: React.FC<LocationFormProps> = ({
  initialData,
  loading = false,
  onSubmit,
}) => {
  const [postalCodeInput, setPostalCodeInput] = useState("");
  const [postalCodeList, setPostalCodeList] = useState<string[]>(
    initialData?.postalCodes ?? [],
  );

  const [invalidPostalCodes, setInvalidPostalCodes] = useState<string[]>([]);

  const formMethods = useForm<LocationFormValues>({
    defaultValues: initialData,
  });

  const {
    handleSubmit,
    control,
    watch,
    setValue,
    formState: { isSubmitting },
  } = formMethods;

  const nameValue = watch("name");
  const postalCodesValue = watch("postalCodes");

  const isFormComplete =
    Boolean(nameValue?.trim()) && postalCodesValue.length > 0;

  useEffect(() => {
    setValue("postalCodes", postalCodeList);
  }, [postalCodeList, setValue]);

  const validateZipCode = (zip: string): boolean => /^\d{5}$/.test(zip);

  const handleAddZipCodes = () => {
    if (!postalCodeInput.trim()) return;

    const entries = postalCodeInput
      .split(",")
      .map((e) => e.trim())
      .filter((e) => e.length > 0);

    const valid: string[] = [];
    const invalid: string[] = [];

    entries.forEach((entry) => {
      if (validateZipCode(entry)) {
        if (!postalCodeList.includes(entry) && !valid.includes(entry)) {
          valid.push(entry);
        }
      } else {
        invalid.push(entry);
      }
    });

    if (valid.length > 0) {
      setPostalCodeList((prev) => [...prev, ...valid]);
    }

    setInvalidPostalCodes(invalid);
    setPostalCodeInput("");
  };

  const handleRemovePostalCode = (zipToRemove: string) => {
    setPostalCodeList((prevList) =>
      prevList.filter((zip) => zip !== zipToRemove),
    );
  };

  const handleFormSubmit = (data: LocationFormValues) => {
    onSubmit(data);
  };

  return (
    <Form {...formMethods}>
      <form className="space-y-4" onSubmit={handleSubmit(handleFormSubmit)}>
        <FormField
          control={control}
          name="name"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Name</FormLabel>
              <FormControl>
                <Input {...field} placeholder="Enter location name" />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="description"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Description</FormLabel>
              <FormControl>
                <Textarea
                  {...field}
                  placeholder="Optional description for the location"
                  rows={3}
                  value={field.value ?? ""}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <div className="w-full max-w-2xl" id="add-zip-codes">
          <FormItem>
            <FormLabel
              className={cn(
                "mb-0",
                invalidPostalCodes.length > 0 && "text-destructive",
              )}
            >
              Add Postal Codes
            </FormLabel>
            <div className="flex gap-2">
              <Input
                className="flex-1 mb-2"
                onChange={(e) => setPostalCodeInput(e.target.value)}
                placeholder="Enter postal codes"
                value={postalCodeInput}
              />
              <Button className="h-9" onClick={handleAddZipCodes} type="button">
                Add
              </Button>
            </div>
          </FormItem>

          {invalidPostalCodes.length > 0 && (
            <div className="text-sm text-destructive font-medium space-y-1">
              <div>
                The following postal codes are invalid and were skipped:
              </div>
              <ul className="list-disc list-inside pl-4">
                {invalidPostalCodes.map((zip, idx) => (
                  <li key={idx}>{zip}</li>
                ))}
              </ul>
            </div>
          )}

          <div className="flex flex-wrap gap-2 mt-4">
            {postalCodeList.map((postalCodeItem, index) => (
              <Badge
                className="truncate flex items-center space-x-1"
                key={index}
              >
                <span className="text-white">{postalCodeItem}</span>
                <X
                  className="h-3 w-3 cursor-pointer text-white"
                  onClick={() => handleRemovePostalCode(postalCodeItem)}
                />
              </Badge>
            ))}
          </div>
        </div>

        <div className="flex justify-end">
          <Button
            disabled={loading || isSubmitting || !isFormComplete}
            type="submit"
          >
            {loading || isSubmitting ? "Saving..." : "Save"}
          </Button>
        </div>
      </form>
    </Form>
  );
};

export default LocationForm;
