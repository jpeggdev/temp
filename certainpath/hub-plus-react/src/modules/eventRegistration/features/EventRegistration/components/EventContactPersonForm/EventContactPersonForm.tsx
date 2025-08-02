import React from "react";
import { useFormContext } from "react-hook-form";
import { Mail, User, Phone } from "lucide-react";
import "react-phone-number-input/style.css";
import PhoneInput from "react-phone-number-input/input";

import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";

import {
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";

import { EventRegistrationFormData } from "@/modules/eventRegistration/features/EventRegistration/hooks/useEventRegistration";

function EventContactPersonForm() {
  const { control } = useFormContext<EventRegistrationFormData>();

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center">
          <Mail className="h-5 w-5 mr-2 text-primary" />
          Event Contact Person
        </CardTitle>
        <CardDescription>
          Designate a contact person to receive all event communications for
          your organization
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div className="space-y-4">
          <FormField
            control={control}
            name="contactName"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Name</FormLabel>
                <FormControl>
                  <div className="flex items-center">
                    <User className="h-4 w-4 mr-2 text-muted-foreground" />
                    <Input
                      {...field}
                      id="contact-name"
                      placeholder="Full Name"
                    />
                  </div>
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="contactEmail"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                  <div className="flex items-center">
                    <Mail className="h-4 w-4 mr-2 text-muted-foreground" />
                    <Input
                      {...field}
                      id="contact-email"
                      placeholder="email@example.com"
                    />
                  </div>
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={control}
            name="contactPhone"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Phone (optional)</FormLabel>
                <FormControl>
                  <div className="flex items-center">
                    <Phone className="h-4 w-4 mr-2 text-muted-foreground" />
                    <div className="flex-1">
                      <PhoneInput
                        className="w-full border rounded h-10 px-3 py-2"
                        country="US"
                        onChange={field.onChange}
                        placeholder="(123) 456-7890"
                        value={field.value}
                      />
                    </div>
                  </div>
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="text-xs text-muted-foreground">
            This person will receive all communications related to this event,
            including registration confirmations, updates, and important
            notifications.
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

export default EventContactPersonForm;
