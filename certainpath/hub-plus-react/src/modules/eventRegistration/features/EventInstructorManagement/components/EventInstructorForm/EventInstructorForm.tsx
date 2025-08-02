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
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { Phone, Mail, User } from "lucide-react";
import "react-phone-number-input/style.css";
import PhoneInput from "react-phone-number-input/input";

const formSchema = z.object({
  name: z.string().min(1, "Name is required"),
  email: z.string().email("Please enter a valid email address"),
  phone: z.string().optional(),
});

export type EventInstructorFormValues = z.infer<typeof formSchema>;

interface EventInstructorFormProps {
  initialData: EventInstructorFormValues;
  loading?: boolean;
  onSubmit: (values: EventInstructorFormValues) => void;
}

const EventInstructorForm: React.FC<EventInstructorFormProps> = ({
  initialData,
  loading = false,
  onSubmit,
}) => {
  const formMethods = useForm<EventInstructorFormValues>({
    resolver: zodResolver(formSchema),
    defaultValues: initialData,
  });

  const {
    handleSubmit,
    control,
    formState: { isSubmitting, isValid },
  } = formMethods;

  const handleFormSubmit = (data: EventInstructorFormValues) => {
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
              <FormLabel>Full Name</FormLabel>
              <FormControl>
                <div className="flex items-center">
                  <User className="h-4 w-4 mr-2 text-muted-foreground" />
                  <Input
                    {...field}
                    placeholder="Enter instructor's full name"
                  />
                </div>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="email"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Email Address</FormLabel>
              <FormControl>
                <div className="flex items-center">
                  <Mail className="h-4 w-4 mr-2 text-muted-foreground" />
                  <Input
                    {...field}
                    placeholder="Enter instructor's email address"
                    type="email"
                  />
                </div>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={control}
          name="phone"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Phone Number (Optional)</FormLabel>
              <FormControl>
                <div className="flex items-center">
                  <Phone className="h-4 w-4 mr-2 text-muted-foreground" />
                  <div className="flex-1">
                    <PhoneInput
                      className="w-full border rounded h-10 px-3 py-2"
                      country="US"
                      onChange={field.onChange}
                      placeholder="(123) 456-7890"
                      value={field.value || ""}
                    />
                  </div>
                </div>
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <div className="flex justify-end">
          <Button disabled={loading || isSubmitting || !isValid} type="submit">
            {loading || isSubmitting ? "Saving..." : "Save"}
          </Button>
        </div>
      </form>
    </Form>
  );
};

export default EventInstructorForm;
