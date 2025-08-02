import { useCallback, useEffect, useState } from "react";
import { useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useNotification } from "@/context/NotificationContext";
import { AppDispatch } from "@/app/store";
import {
  VoucherFormData,
  VoucherFormSchema,
} from "@/modules/eventRegistration/features/EventVoucherManagement/hooks/VoucherFormSchema";
import { createVoucherAction } from "@/modules/eventRegistration/features/EventVoucherManagement/slices/VoucherSlice";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import { validateVoucherName } from "@/modules/eventRegistration/features/EventVoucherManagement/api/validateVoucherName/validateVoucherNameApi";

export function useCreateVoucher() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();

  const [isLoading, setIsLoading] = useState(false);
  const [voucherNameCheckStatus, setVoucherNameCheckStatus] = useState<
    "idle" | "loading" | "valid" | "invalid"
  >("idle");
  const [voucherNameCheckMessage, setVoucherNameCheckMessage] = useState("");

  const defaultValues: VoucherFormData = {
    name: "",
    company: null,
    description: null,
    totalSeats: 10,
    isActive: true,
    startDate: null,
    endDate: null,
  };

  const form = useForm<VoucherFormData>({
    resolver: zodResolver(VoucherFormSchema),
    defaultValues,
    mode: "onChange",
    shouldUnregister: true,
  });

  const { watch } = form;
  const voucherNameValue = watch("name") || "";
  const debouncedVoucherName = useDebouncedValue(voucherNameValue, 500);

  useEffect(() => {
    if (!debouncedVoucherName) {
      setVoucherNameCheckStatus("idle");
      setVoucherNameCheckMessage("");
      return;
    }

    setVoucherNameCheckStatus("loading");
    validateVoucherName({ name: debouncedVoucherName })
      .then((res) => {
        if (res.data.nameExists) {
          setVoucherNameCheckStatus("invalid");
          setVoucherNameCheckMessage(
            res.data.message || "Voucher name is already in use.",
          );
        } else {
          setVoucherNameCheckStatus("valid");
          setVoucherNameCheckMessage("Voucher name is available!");
        }
      })
      .catch((err) => {
        console.error("Error validating voucher name:", err);
        setVoucherNameCheckStatus("invalid");
        setVoucherNameCheckMessage(
          "Error validating voucher name. Please try again or choose another.",
        );
      });
  }, [debouncedVoucherName]);

  const handleDateChange =
    (fieldName: "startDate" | "endDate") => (selectedDate: Date | null) => {
      if (!selectedDate) {
        form.setValue(fieldName, "");
      } else {
        form.setValue(fieldName, selectedDate.toISOString());
      }
    };

  function parseIsoString(value: string | null | undefined): Date | null {
    try {
      if (!value) return null;
      return new Date(value);
    } catch {
      return null;
    }
  }

  const submitForm = useCallback(
    async (values: VoucherFormData) => {
      try {
        setIsLoading(true);

        const requestData = {
          name: values.name,
          companyIdentifier: values.company?.companyIdentifier || "",
          description: values.description,
          totalSeats: values.totalSeats,
          isActive: values.isActive,
          startDate: values.startDate === "" ? null : values.startDate,
          endDate: values.endDate === "" ? null : values.endDate,
        };

        await dispatch(
          createVoucherAction(requestData, () => {
            showNotification(
              "Success!",
              "Voucher has been successfully created!",
              "success",
            );
            navigate(`/event-registration/admin/vouchers`);
          }),
        );
      } catch (error) {
        console.error("Error saving voucher:", error);
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

  const handleCancelCreateVoucher = (e: React.FormEvent) => {
    e.preventDefault();
    navigate("/event-registration/admin/vouchers");
  };

  return {
    form,
    submitForm,
    isLoading,
    voucherNameCheckStatus,
    voucherNameCheckMessage,
    parseIsoString,
    handleDateChange,
    handleCancelCreateVoucher,
  };
}
