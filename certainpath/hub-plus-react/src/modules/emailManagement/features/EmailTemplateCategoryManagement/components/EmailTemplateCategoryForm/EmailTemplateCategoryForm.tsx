import React from "react";
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

import {
  Select,
  SelectTrigger,
  SelectContent,
  SelectItem,
  SelectValue,
} from "@/components/ui/select";

export interface EmailTemplateCategoryFormValues {
  name: string;
  displayedName: string;
  description: string | null;
  colorId: number;
}

export interface ColorItem {
  id: number | null;
  value: string | null;
}

interface EmailTemplateCategoryFormProps {
  initialData: EmailTemplateCategoryFormValues;
  loading?: boolean;
  onSubmit: (values: EmailTemplateCategoryFormValues) => void;
  availableColors: ColorItem[];
}

const EmailTemplateCategoryForm: React.FC<EmailTemplateCategoryFormProps> = ({
  initialData,
  loading = false,
  onSubmit,
  availableColors,
}) => {
  const formMethods = useForm<EmailTemplateCategoryFormValues>({
    defaultValues: initialData,
  });

  const {
    handleSubmit,
    control,
    formState: { isSubmitting },
    watch,
  } = formMethods;

  const nameValue = watch("name");
  const displayedNameValue = watch("displayedName");

  const handleFormSubmit = (data: EmailTemplateCategoryFormValues) => {
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
              <FormLabel>Internal Name</FormLabel>
              <FormControl>
                <Input {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="displayedName"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Displayed Name</FormLabel>
              <FormControl>
                <Input {...field} />
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
                  placeholder="Optional description for the category"
                  rows={3}
                  value={field.value ?? ""}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="colorId"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Color</FormLabel>
              <FormControl>
                <Select
                  onValueChange={(val) => field.onChange(Number(val))}
                  value={String(field.value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select a color" />
                  </SelectTrigger>
                  <SelectContent>
                    {availableColors.map((color) => {
                      if (color.id == null || color.value == null) {
                        return null;
                      }
                      return (
                        <SelectItem key={color.id} value={String(color.id)}>
                          <span
                            className="mr-2 inline-block h-3 w-3 rounded-full"
                            style={{ backgroundColor: color.value }}
                          />
                          {color.value}
                        </SelectItem>
                      );
                    })}
                  </SelectContent>
                </Select>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <div className="flex justify-end">
          <Button
            disabled={
              loading ||
              isSubmitting ||
              !nameValue?.trim() ||
              !displayedNameValue?.trim()
            }
            type="submit"
          >
            {loading || isSubmitting ? "Saving..." : "Save"}
          </Button>
        </div>
      </form>
    </Form>
  );
};

export default EmailTemplateCategoryForm;
