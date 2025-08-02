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

export interface ResourceTagFormValues {
  name: string;
}

interface ResourceTagFormProps {
  initialData: ResourceTagFormValues;
  loading?: boolean;
  onSubmit: (values: ResourceTagFormValues) => void;
}

const ResourceTagForm: React.FC<ResourceTagFormProps> = ({
  initialData,
  loading = false,
  onSubmit,
}) => {
  const formMethods = useForm<ResourceTagFormValues>({
    defaultValues: initialData,
  });

  const {
    handleSubmit,
    control,
    formState: { isSubmitting },
    watch,
  } = formMethods;

  const nameValue = watch("name");

  const handleFormSubmit = (data: ResourceTagFormValues) => {
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
              <FormLabel>Tag Name</FormLabel>
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

export default ResourceTagForm;
