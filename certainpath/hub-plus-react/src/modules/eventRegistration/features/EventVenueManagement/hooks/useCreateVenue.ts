import { useCallback, useState } from "react";
import { useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useNotification } from "@/context/NotificationContext";
import { AppDispatch } from "@/app/store";
import {
  VenueFormData,
  VenueFormSchema,
} from "@/modules/eventRegistration/features/EventVenueManagement/hooks/VenueFormSchema";
import { createVenueAction } from "@/modules/eventRegistration/features/EventVenueManagement/slices/VenueSlice";

export function useCreateVenue() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();

  const [isLoading, setIsLoading] = useState(false);

  const defaultValues: VenueFormData = {
    name: "",
    description: null,
    address: "",
    address2: null,
    city: "",
    state: "",
    postalCode: "",
    country: "",
    isActive: true,
  };

  const form = useForm<VenueFormData>({
    resolver: zodResolver(VenueFormSchema),
    defaultValues,
    mode: "onChange",
  });

  const submitForm = useCallback(
    async (values: VenueFormData) => {
      try {
        setIsLoading(true);

        const requestData = {
          name: values.name,
          description: values.description,
          address: values.address,
          address2: values.address2,
          city: values.city,
          state: values.state,
          postalCode: values.postalCode,
          country: values.country,
          isActive: values.isActive,
        };

        await dispatch(
          createVenueAction(requestData, () => {
            showNotification(
              "Success!",
              "Event Venue has been successfully created!",
              "success",
            );
            navigate(`/event-registration/admin/venues`);
          }),
        );
      } catch (error) {
        console.error("Error saving event venue:", error);
        if (error instanceof Error) {
          showNotification(
            "Error!",
            error.message || "Failed to save",
            "error",
          );
        } else {
          showNotification(
            "Error!",
            "An unknown error occurred while saving.",
            "error",
          );
        }
      } finally {
        setIsLoading(false);
      }
    },
    [dispatch, navigate, showNotification],
  );

  const handleCancelCreateVenue = (e: React.FormEvent) => {
    e.preventDefault();
    navigate("/event-registration/admin/venues");
  };

  return {
    form,
    submitForm,
    isLoading,
    handleCancelCreateVenue,
  };
}
