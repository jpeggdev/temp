import React, { useCallback, useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useNotification } from "@/context/NotificationContext";
import { AppDispatch } from "@/app/store";
import {
  DiscountFormData,
  DiscountFormSchema,
} from "@/modules/eventRegistration/features/EventDiscountManagement/hooks/DiscountFormSchema";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import { createDiscountAction } from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountSlice";
import { RootState } from "@/app/rootReducer";
import { fetchDiscountMetadataAction } from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountMetadataSlice";
import { validateDiscountCode } from "@/modules/eventRegistration/features/EventDiscountManagement/api/validateDiscountCode/validateDiscountCodeApi";

export function useCreateDiscount() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();

  const [isLoading, setIsLoading] = useState(false);

  const [discountCodeCheckStatus, setDiscountCodeCheckStatus] = useState<
    "idle" | "loading" | "valid" | "invalid"
  >("idle");
  const [discountCodeCheckMessage, setDiscountCodeCheckMessage] = useState("");

  const defaultValues: DiscountFormData = {
    code: "",
    description: null,
    discountValue: "",
    discountType: null,
    maxUses: null,
    minPurchaseAmount: "",
    isActive: true,
    events: null,
    startDate: null,
    endDate: null,
  };

  const form = useForm<DiscountFormData>({
    resolver: zodResolver(DiscountFormSchema),
    defaultValues,
    mode: "onChange",
  });

  const { watch } = form;
  const discountCodeValue = watch("code") || "";
  const debouncedDiscountCode = useDebouncedValue(discountCodeValue, 500);

  const { discountMetadata } = useSelector(
    (state: RootState) => state.discountMetadata,
  );

  useEffect(() => {
    if (!debouncedDiscountCode) {
      setDiscountCodeCheckStatus("idle");
      setDiscountCodeCheckMessage("");
      return;
    }

    setDiscountCodeCheckStatus("loading");
    validateDiscountCode({ code: debouncedDiscountCode })
      .then((res) => {
        if (res.data.codeExists) {
          setDiscountCodeCheckStatus("invalid");
          setDiscountCodeCheckMessage(
            res.data.message || "Discount code is already in use.",
          );
        } else {
          setDiscountCodeCheckStatus("valid");
          setDiscountCodeCheckMessage("Discount code is available!");
        }
      })
      .catch((err) => {
        console.error("Error validating discount code:", err);
        setDiscountCodeCheckStatus("invalid");
        setDiscountCodeCheckMessage(
          "Error validating discount code. Please try again or choose another.",
        );
      });
  }, [debouncedDiscountCode]);

  useEffect(() => {
    dispatch(fetchDiscountMetadataAction());
  }, [dispatch]);

  useEffect(() => {
    if (discountMetadata?.discountTypes && !watch("discountType")) {
      const defaultDiscountType = discountMetadata.discountTypes.find(
        (type) => type.isDefault,
      );
      if (defaultDiscountType) {
        form.setValue("discountType", defaultDiscountType);
      }
    }
  }, [discountMetadata, form, watch]);

  const submitForm = useCallback(
    async (values: DiscountFormData) => {
      try {
        setIsLoading(true);

        const eventIds = values.events?.map((event) => event.id) ?? [];

        const requestData = {
          code: values.code,
          description: values.description,
          discountTypeId: values.discountType!.id,
          discountValue: values.discountValue,
          maximumUses: values.maxUses ? Number(values.maxUses) : null,
          minimumPurchaseAmount: values.minPurchaseAmount,
          isActive: values.isActive,
          eventIds: eventIds,
          startDate: values.startDate === "" ? null : values.startDate,
          endDate: values.endDate === "" ? null : values.endDate,
        };

        await dispatch(
          createDiscountAction(requestData, () => {
            showNotification(
              "Success!",
              "Event Discount has been successfully created!",
              "success",
            );
            navigate(`/event-registration/admin/discounts`);
          }),
        );
      } catch (error) {
        console.error("Error saving event discount:", error);
        if (error instanceof Error) {
          showNotification(
            "Error!",
            error.message || "Failed to create",
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

  const handleCancelCreateDiscount = (e: React.FormEvent) => {
    e.preventDefault();
    navigate("/event-registration/admin/discounts");
  };

  return {
    form,
    submitForm,
    isLoading,
    discountMetadata,
    discountCodeCheckStatus,
    discountCodeCheckMessage,
    handleCancelCreateDiscount,
  };
}
