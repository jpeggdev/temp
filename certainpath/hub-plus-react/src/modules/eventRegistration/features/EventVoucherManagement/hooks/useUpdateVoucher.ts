import { useCallback, useEffect, useState } from "react";
import { useDispatch } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useNavigate } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useAppSelector } from "@/app/hooks";
import {
  VoucherFormData,
  VoucherFormSchema,
} from "@/modules/eventRegistration/features/EventVoucherManagement/hooks/VoucherFormSchema";
import {
  fetchVoucherAction,
  updateVoucherAction,
} from "@/modules/eventRegistration/features/EventVoucherManagement/slices/VoucherSlice";
import { validateVoucherName } from "@/modules/eventRegistration/features/EventVoucherManagement/api/validateVoucherName/validateVoucherNameApi";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";

export function useUpdateVoucher() {
  const navigate = useNavigate();
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();
  const [isLoading, setIsLoading] = useState(false);
  const [lastResetName, setLastResetName] = useState("");
  const [voucherNameCheckStatus, setVoucherNameCheckStatus] = useState<
    "idle" | "loading" | "valid" | "invalid"
  >("idle");
  const [voucherNameCheckMessage, setVoucherNameCheckMessage] = useState("");

  const { fetchedVoucher, loadingUpdate, loadingFetch } = useAppSelector(
    (state) => state.voucher,
  );

  const defaultValues: VoucherFormData = {
    name: "",
    description: "",
    totalSeats: 0,
    isActive: true,
    startDate: "",
    endDate: "",
    company: null,
  };

  const form = useForm<VoucherFormData>({
    resolver: zodResolver(VoucherFormSchema),
    defaultValues,
    mode: "onChange",
  });

  useEffect(() => {
    if (fetchedVoucher) {
      const name = fetchedVoucher.name || "";
      form.reset({
        name,
        description: fetchedVoucher.description || null,
        totalSeats: fetchedVoucher.totalSeats || 0,
        isActive: fetchedVoucher.isActive,
        startDate: fetchedVoucher.startDate || null,
        endDate: fetchedVoucher.endDate || null,
        company: {
          id: fetchedVoucher.company.id,
          name: fetchedVoucher.company.name,
          companyIdentifier: fetchedVoucher.company.companyIdentifier,
        },
      });
      setLastResetName(name);
    }
  }, [fetchedVoucher, form]);

  const { watch } = form;
  const voucherNameValue = watch("name") || "";
  const debouncedVoucherName = useDebouncedValue(voucherNameValue, 500);

  useEffect(() => {
    if (!debouncedVoucherName || debouncedVoucherName === lastResetName) {
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
  }, [debouncedVoucherName, lastResetName]);

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
      setIsLoading(true);

      try {
        if (!fetchedVoucher) {
          showNotification("Error", "No voucher loaded to update", "error");
          return;
        }

        const voucherId = fetchedVoucher?.id;
        if (!voucherId) {
          showNotification("Error", "No voucher ID found to update", "error");
          return;
        }

        const requestData = {
          name: values.name,
          description: values.description,
          isActive: values.isActive,
          companyIdentifier: values.company?.companyIdentifier || "",
          startDate: values.startDate,
          endDate: values.endDate,
        };

        dispatch(
          updateVoucherAction(voucherId, requestData, () => {
            showNotification(
              "Success!",
              "Voucher has been successfully updated!",
              "success",
            );
            navigate(`/event-registration/admin/vouchers`);
          }),
        );
      } catch (error) {
        console.error("Error updating the voucher:", error);
        if (error instanceof Error) {
          showNotification(
            "Error",
            error.message || "Failed to update the voucher",
            "error",
          );
        } else {
          showNotification(
            "Error",
            "An unknown error occurred while updating the voucher.",
            "error",
          );
        }
      } finally {
        setIsLoading(false);
      }
    },
    [fetchedVoucher, navigate, dispatch, showNotification],
  );

  const fetchVoucher = useCallback(
    (idParam: number) => {
      dispatch(fetchVoucherAction(idParam));
    },
    [dispatch],
  );

  const handleCancelEditVoucher = useCallback(() => {
    navigate("/event-registration/admin/vouchers");
  }, []);

  return {
    form,
    submitForm,
    fetchVoucher,
    handleCancelEditVoucher,
    parseIsoString,
    handleDateChange,
    voucherNameCheckStatus,
    voucherNameCheckMessage,
    voucherName: fetchedVoucher?.name,
    isLoading: isLoading || loadingUpdate || loadingFetch,
  };
}
