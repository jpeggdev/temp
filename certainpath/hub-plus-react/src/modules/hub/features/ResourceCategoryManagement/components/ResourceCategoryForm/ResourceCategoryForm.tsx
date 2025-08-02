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

export interface ResourceCategoryFormValues {
  name: string;
}

interface ResourceCategoryFormProps {
  initialData: ResourceCategoryFormValues;
  loading?: boolean;
  onSubmit: (values: ResourceCategoryFormValues) => void;
}

const ResourceCategoryForm: React.FC<ResourceCategoryFormProps> = ({
  initialData,
  loading = false,
  onSubmit,
}) => {
  const formMethods = useForm<ResourceCategoryFormValues>({
    defaultValues: initialData,
  });

  const {
    handleSubmit,
    control,
    formState: { isSubmitting },
    watch,
  } = formMethods;

  const nameValue = watch("name");

  const handleFormSubmit = (data: ResourceCategoryFormValues) => {
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
              <FormLabel>Category Name</FormLabel>
              <FormControl>
                <Input {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <div className="flex justify-end">
          <Button
            disabled={loading || isSubmitting || !nameValue?.trim()}
            type="submit"
          >
            {loading || isSubmitting ? "Saving..." : "Save"}
          </Button>
        </div>
      </form>
    </Form>
  );
};

export default ResourceCategoryForm;
