import React, { useEffect } from "react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import { useToast } from "@/components/ui/use-toast";
import CampaignProductForm from "../CampaignProductForm/CampaignProductForm";
import {
  createCampaignProductAction,
  updateCampaignProductAction,
  setCurrentProduct,
} from "../../slices/campaignProductsSlice";

import { CreateCampaignProductRequest } from "@/api/createCampaignProduct/types";

interface CampaignProductDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

const CampaignProductDialog: React.FC<CampaignProductDialogProps> = ({
  isOpen,
  onClose,
  onSuccess,
}) => {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { currentProduct, loading } = useSelector(
    (state: RootState) => state.stochasticCampaignProducts,
  );

  const isEditing = !!currentProduct;

  useEffect(() => {
    if (!isOpen) {
      dispatch(setCurrentProduct(null));
    }
  }, [isOpen, dispatch]);

  const handleSubmit = async (data: CreateCampaignProductRequest) => {
    try {
      if (isEditing && currentProduct) {
        await dispatch(updateCampaignProductAction(currentProduct.id, data));
        toast({
          title: "Success",
          description: "Campaign product updated successfully",
          variant: "default",
        });
      } else {
        await dispatch(createCampaignProductAction(data));
        toast({
          title: "Success",
          description: "Campaign product created successfully",
          variant: "default",
        });
      }
      onClose();

      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      toast({
        title: "Error",
        description:
          error instanceof Error ? error.message : "An error occurred",
        variant: "destructive",
      });
    }
  };

  return (
    <Dialog onOpenChange={(open) => !open && onClose()} open={isOpen}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>
            {isEditing ? "Edit Campaign Product" : "Create Campaign Product"}
          </DialogTitle>
        </DialogHeader>
        <CampaignProductForm
          initialData={currentProduct}
          isLoading={loading}
          onCancel={onClose}
          onSubmit={handleSubmit}
        />
      </DialogContent>
    </Dialog>
  );
};

export default CampaignProductDialog;
