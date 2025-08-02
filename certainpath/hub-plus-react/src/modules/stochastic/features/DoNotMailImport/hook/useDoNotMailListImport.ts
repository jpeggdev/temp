import React, { useState, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { AppDispatch } from "@/app/store";
import { useNotification } from "@/context/NotificationContext";
import { useToast } from "@/components/ui/use-toast";
import { uploadDoNotMailRequest } from "@/modules/stochastic/features/DoNotMailImport/api/uploadDoNotMailList/types";
import {
  addToDoNotMailListAction,
  resetRestrictedAddressesAction,
  uploadDoNotMailListAction,
} from "@/modules/stochastic/features/DoNotMailImport/slice/DoNotMailListImportSlice";
import { RootState } from "@/app/rootReducer";
import { bulkCreateRestrictedAddressesRequest } from "@/modules/stochastic/features/DoNotMailImport/api/bulkCreateRestrictedAddresses/types";
import { useNavigate } from "react-router-dom";
import { AxiosError, AxiosResponse } from "axios";
import { extractErrorMessage } from "@/utils/extractErrorMessage";

export function useDoNotMailListImport() {
  const dispatch = useDispatch<AppDispatch>();
  const { toast } = useToast();
  const navigate = useNavigate();
  const { showNotification } = useNotification();

  interface UploadErrorDialogState {
    titleContent: React.ReactNode;
    instructionTitle: string;
    instructionItems: string[];
    instructionFinalQuestion: string;
    cancelMessage: string;
    confirmMessage: string;
    cancelHandler: () => void;
    confirmHandler: () => void;
  }

  const [uploadErrorModalState, setUploadErrorModalState] =
    useState<UploadErrorDialogState>({
      titleContent: "",
      instructionTitle: "",
      instructionItems: [],
      instructionFinalQuestion: "",
      cancelMessage: "",
      confirmMessage: "",
      cancelHandler: () => {},
      confirmHandler: () => {},
    });

  const [file, setFile] = useState<File | null>(null);
  const [isConfirmAddModalOpen, setIsConfirmAddModalOpen] =
    useState<boolean>(false);
  const [showUploadErrorModal, setShowUploadErrorModal] = useState(false);

  const {
    loadingUpload,
    matchesCount,
    loadingCreate,
    uploadProgress,
    addressesMatches,
  } = useSelector((state: RootState) => state.doNotMailImport);

  const handleFileChange = (incomingFile: File) => {
    setFile(incomingFile);
  };

  const handleUpload = useCallback(
    async (values: uploadDoNotMailRequest) => {
      const requestData = {
        file: values.file,
      };

      try {
        await dispatch(
          uploadDoNotMailListAction(requestData, () => {
            showNotification(
              "Success!",
              "The Do Not Mail list uploaded successfully!",
              "success",
            );
          }),
        );
      } catch (error) {
        await handleUploadError(error as AxiosError);
      }
    },
    [toast, dispatch, showNotification],
  );

  const handleCloseModal = () => {
    setShowUploadErrorModal(false);
  };

  const handleUploadError = async (error: AxiosError) => {
    console.error("Handling upload error:", error);

    let errorMessage = "An error occurred.";
    if (error.response) {
      errorMessage = await extractErrorMessage(error.response as AxiosResponse);
    }

    setUploadErrorModalState({
      titleContent: "Upload Error",
      instructionTitle: errorMessage,
      instructionItems: [],
      instructionFinalQuestion: "",
      cancelMessage: "Close",
      confirmMessage: "",
      cancelHandler: handleCloseModal,
      confirmHandler: () => {},
    });
    setShowUploadErrorModal(true);
  };

  const handleAddToDoNotMailList = useCallback(async () => {
    const requestData: bulkCreateRestrictedAddressesRequest = {
      addresses: addressesMatches ?? [],
    };

    try {
      dispatch(addToDoNotMailListAction(requestData));

      showNotification(
        "Success!",
        "The addresses added to the do not mail list successfully!",
        "success",
      );

      dispatch(resetRestrictedAddressesAction());
      setFile(null);
      setIsConfirmAddModalOpen(false);

      navigate("/stochastic/do-not-mail");
    } catch (error) {
      const errorMessage =
        error instanceof Error ? error.message : "An unknown error occurred.";
      toast({
        title: "Error",
        description: errorMessage,
        variant: "destructive",
      });
    }
  }, [dispatch, showNotification, toast, navigate]);

  const handleAddToDoNotMailListButtonClick = () => {
    setIsConfirmAddModalOpen(true);
  };

  const handleCloseConfirmModalButtonClick = () => {
    setIsConfirmAddModalOpen(false);
  };

  const handleCancelAddToDoNotMailList = () => {
    setFile(null);
    dispatch(resetRestrictedAddressesAction());
  };

  return {
    file,
    uploadErrorModalState,
    matchesCount,
    loadingUpload,
    loadingCreate,
    handleUpload,
    uploadProgress,
    addressesMatches,
    isConfirmAddModalOpen,
    handleAddToDoNotMailList,
    showUploadErrorModal,
    handleFileChange,
    handleCancelAddToDoNotMailList,
    handleCloseConfirmModalButtonClick,
    handleAddToDoNotMailListButtonClick,
  };
}
