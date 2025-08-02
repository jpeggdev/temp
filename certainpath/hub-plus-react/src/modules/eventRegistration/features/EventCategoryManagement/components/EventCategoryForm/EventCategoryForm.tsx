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
import { Switch } from "@/components/ui/switch";
import { Textarea } from "@/components/ui/textarea";

export interface EventCategoryFormValues {
  name: string;
  description?: string | null;
  isActive?: boolean;
}

interface EventCategoryFormProps {
  initialData: EventCategoryFormValues;
  loading?: boolean;
  onSubmit: (values: EventCategoryFormValues) => void;
}

const EventCategoryForm: React.FC<EventCategoryFormProps> = ({
  initialData,
  loading = false,
  onSubmit,
}) => {
  const formMethods = useForm<EventCategoryFormValues>({
    defaultValues: initialData,
  });

  const {
    handleSubmit,
    control,
    formState: { isSubmitting },
    watch,
  } = formMethods;

  const nameValue = watch("name");

  const handleFormSubmit = (data: EventCategoryFormValues) => {
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

        <FormField
          control={control}
          name="description"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Description</FormLabel>
              <FormControl>
                <Textarea
                  {...field}
                  placeholder="Optional category description"
                  rows={4}
                  value={field.value ?? ""}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="isActive"
          render={({ field }) => (
            <FormItem className="flex items-center justify-between rounded-md p-2 border">
              <div className="space-y-1">
                <FormLabel className="text-base">Active?</FormLabel>
                <FormMessage />
              </div>
              <FormControl>
                <Switch
                  checked={field.value ?? false}
                  onCheckedChange={field.onChange}
                />
              </FormControl>
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

export default EventCategoryForm;
