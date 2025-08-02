import { useCallback, useEffect, useState } from "react";
import { useDispatch } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useAppSelector } from "@/app/hooks";
import {
  VenueFormData,
  VenueFormSchema,
} from "@/modules/eventRegistration/features/EventVenueManagement/hooks/VenueFormSchema";
import {
  fetchVenueAction,
  updateVenueAction,
} from "@/modules/eventRegistration/features/EventVenueManagement/slices/VenueSlice";

export function useEditVenue() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();
  const [isLoading, setIsLoading] = useState(false);

  const { fetchedVenue, loadingUpdate, loadingFetch } = useAppSelector(
    (state) => state.venue,
  );

  const defaultValues: VenueFormData = {
    name: "",
    description: "",
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

  useEffect(() => {
    if (fetchedVenue) {
      form.reset({
        name: fetchedVenue.name || "",
        description: fetchedVenue.description || null,
        address: fetchedVenue.address,
        address2: fetchedVenue.address2 || null,
        city: fetchedVenue.city || "",
        state: fetchedVenue.state || "",
        postalCode: fetchedVenue.postalCode || "",
        country: fetchedVenue.country || "",
        isActive: fetchedVenue.isActive || true,
      });
    }
  }, [fetchedVenue, form]);

  const submitForm = useCallback(
    async (values: VenueFormData) => {
      setIsLoading(true);

      try {
        if (!fetchedVenue) {
          showNotification("Error", "No venue loaded to update", "error");
          return;
        }

        const venueId = fetchedVenue?.id;
        if (!venueId) {
          showNotification("Error", "No venue ID found to update", "error");
          return;
        }

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

        dispatch(
          updateVenueAction(venueId, requestData, () => {
            showNotification(
              "Success!",
              "Venue has been successfully updated!",
              "success",
            );
            navigate(`/event-registration/admin/venues`);
          }),
        );
      } catch (error) {
        console.error("Error updating the venue:", error);
        if (error instanceof Error) {
          showNotification(
            "Error",
            error.message || "Failed to update the venue",
            "error",
          );
        } else {
          showNotification(
            "Error",
            "An unknown error occurred while updating the venue.",
            "error",
          );
        }
      } finally {
        setIsLoading(false);
      }
    },
    [fetchedVenue, navigate, dispatch, showNotification],
  );

  const fetchVenue = useCallback(
    (idParam: number) => {
      dispatch(fetchVenueAction(idParam));
    },
    [dispatch],
  );

  const handleCancelEditVenue = useCallback(() => {
    navigate("/event-registration/admin/venues");
  }, []);

  return {
    form,
    submitForm,
    fetchVenue,
    handleCancelEditVenue,
    venueName: fetchedVenue?.name,
    isLoading: isLoading || loadingUpdate || loadingFetch,
  };
}
