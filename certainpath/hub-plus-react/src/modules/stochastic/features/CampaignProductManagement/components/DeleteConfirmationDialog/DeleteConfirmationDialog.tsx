import React from "react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { useToast } from "@/components/ui/use-toast";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import { deleteCampaignProductAction } from "../../slices/campaignProductsSlice";

interface DeleteConfirmationDialogProps {
  isOpen: boolean;
  onClose: () => void;
  productId: string | number | null;
  productName: string;
  onSuccess?: () => void;
}

const DeleteConfirmationDialog: React.FC<DeleteConfirmationDialogProps> = ({
  isOpen,
  onClose,
  productId,
  productName,
  onSuccess,
}) => {
  const dispatch = useDispatch();
  const { toast } = useToast();
  const { loading } = useSelector(
    (state: RootState) => state.stochasticCampaignProducts,
  );

  const handleConfirmDelete = async () => {
    if (!productId) return;

    try {
      await dispatch(deleteCampaignProductAction(productId));
      toast({
        title: "Success",
        description: "Campaign product deleted successfully",
        variant: "default",
      });
      onClose();

      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      toast({
        title: "Error",
        description:
          error instanceof Error
            ? error.message
            : "Failed to delete campaign product",
        variant: "destructive",
      });
    }
  };

  return (
    <Dialog onOpenChange={(open) => !open && onClose()} open={isOpen}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Delete Campaign Product</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete `{productName}`? This action cannot
            be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button disabled={loading} onClick={onClose} variant="outline">
            Cancel
          </Button>
          <Button
            disabled={loading}
            onClick={handleConfirmDelete}
            variant="destructive"
          >
            {loading ? "Deleting..." : "Delete"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

export default DeleteConfirmationDialog;
