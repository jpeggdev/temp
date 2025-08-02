import { useCallback, useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useAppSelector } from "@/app/hooks";
import {
  DiscountFormData,
  DiscountFormSchema,
} from "@/modules/eventRegistration/features/EventDiscountManagement/hooks/DiscountFormSchema";
import {
  fetchDiscountAction,
  updateDiscountAction,
} from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountSlice";
import { fetchDiscountMetadataAction } from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountMetadataSlice";
import { RootState } from "@/app/rootReducer";

export function useEditDiscount() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();
  const [isLoading, setIsLoading] = useState(false);

  const { fetchedDiscount, loadingUpdate, loadingFetch } = useAppSelector(
    (state) => state.discount,
  );

  const { discountMetadata, loadingFetch: LoadingMetadata } = useSelector(
    (state: RootState) => state.discountMetadata,
  );

  const defaultValues: DiscountFormData = {
    code: "",
    description: "",
    discountType: null,
    discountValue: "",
    maxUses: null,
    minPurchaseAmount: "",
    isActive: true,
    events: null,
    startDate: "",
    endDate: "",
  };

  const form = useForm<DiscountFormData>({
    resolver: zodResolver(DiscountFormSchema),
    defaultValues,
    mode: "onChange",
  });

  useEffect(() => {
    if (fetchedDiscount) {
      form.reset({
        code: fetchedDiscount.code || "",
        description: fetchedDiscount.description || null,
        discountType: {
          id: fetchedDiscount.discountType.id,
          name: fetchedDiscount.discountType.name,
        },
        discountValue: fetchedDiscount.discountValue || "",
        maxUses: String(fetchedDiscount.maximumUses) || null,
        minPurchaseAmount: fetchedDiscount.minimumPurchaseAmount || "",
        isActive: fetchedDiscount.isActive || null,
        startDate:
          fetchedDiscount.startDate === "" ? null : fetchedDiscount.startDate,
        endDate:
          fetchedDiscount.endDate === "" ? null : fetchedDiscount.endDate,
        events:
          fetchedDiscount.events
            ?.filter((e) => e.id !== null)
            .map((e) => ({
              id: e.id as number,
              name: e.eventName,
            })) || [],
      });
    }
  }, [fetchedDiscount, form]);

  useEffect(() => {
    dispatch(fetchDiscountMetadataAction());
  }, [dispatch]);

  const submitForm = useCallback(
    async (values: DiscountFormData) => {
      setIsLoading(true);

      try {
        if (!fetchedDiscount) {
          showNotification("Error", "No discount loaded to update", "error");
          return;
        }

        const discountId = fetchedDiscount?.id;
        if (!discountId) {
          showNotification("Error", "No discount ID found to update", "error");
          return;
        }

        const eventIds = values.events?.map((event) => event.id) ?? [];

        const requestData = {
          code: values.code,
          description: values.description,
          discountTypeId: values.discountType!.id,
          discountValue: values.discountValue,
          minimumPurchaseAmount: values.minPurchaseAmount,
          isActive: values.isActive,
          eventIds: eventIds,
          startDate: values.startDate === "" ? null : values.startDate,
          endDate: values.endDate === "" ? null : values.endDate,
        };

        dispatch(
          updateDiscountAction(discountId, requestData, () => {
            showNotification(
              "Success",
              "Discount has been successfully updated!",
              "success",
            );
            navigate(`/event-registration/admin/discounts`);
          }),
        );
      } catch (error) {
        console.error("Error updating the discount:", error);
        if (error instanceof Error) {
          showNotification(
            "Error",
            error.message || "Failed to update the discount",
            "error",
          );
        } else {
          showNotification(
            "Error",
            "An unknown error occurred while updating the discount.",
            "error",
          );
        }
      } finally {
        setIsLoading(false);
      }
    },
    [fetchedDiscount, navigate, dispatch, showNotification],
  );

  const fetchDiscount = useCallback(
    (idParam: number) => {
      dispatch(fetchDiscountAction(idParam));
    },
    [dispatch],
  );

  const handleCancelEditDiscount = useCallback(() => {
    navigate("/event-registration/admin/discounts");
  }, []);

  return {
    form,
    submitForm,
    fetchDiscount,
    discountMetadata,
    handleCancelEditDiscount,
    discountCode: fetchedDiscount?.code,
    isLoading: isLoading || LoadingMetadata || loadingUpdate || loadingFetch,
  };
}
