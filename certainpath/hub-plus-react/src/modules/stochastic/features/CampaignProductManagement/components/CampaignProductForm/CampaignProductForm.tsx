import React, { useEffect } from "react";
import { useForm } from "react-hook-form";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import { CreateCampaignProductRequest } from "@/api/createCampaignProduct/types";

interface CampaignProductFormProps {
  initialData?: CampaignProduct | null;
  onSubmit: (data: CreateCampaignProductRequest) => Promise<void>;
  onCancel: () => void;
  isLoading: boolean;
}

const CampaignProductForm: React.FC<CampaignProductFormProps> = ({
  initialData,
  onSubmit,
  onCancel,
  isLoading,
}) => {
  const isEditing = !!initialData;

  const getDefaultValues = (
    product: CampaignProduct | null | undefined,
  ): CreateCampaignProductRequest => {
    if (!product) {
      return {
        name: "",
        description: "",
        isActive: true,
        prospectPrice: undefined,
        customerPrice: undefined,
        category: "",
      };
    }

    return {
      name: product.name || "",
      description: product.description || "",
      prospectPrice: product.prospectPrice,
      customerPrice: product.customerPrice,
      isActive: product.isActive !== false,
      category: product.category || "",
    };
  };

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<CreateCampaignProductRequest>({
    defaultValues: getDefaultValues(initialData),
  });

  useEffect(() => {
    const defaultValues = getDefaultValues(initialData);
    reset(defaultValues);
  }, [initialData, reset]);

  const onFormSubmit = async (data: CreateCampaignProductRequest) => {
    try {
      await onSubmit(data);
    } catch (error) {
      console.error("Form submission error:", error);
    }
  };

  return (
    <form className="space-y-6" onSubmit={handleSubmit(onFormSubmit)}>
      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1" htmlFor="name">
            Name *
          </label>
          <Input
            id="name"
            {...register("name", { required: "Name is required" })}
          />
          {errors.name && (
            <p className="text-red-500 text-sm mt-1">{errors.name.message}</p>
          )}
        </div>

        <div>
          <label
            className="block text-sm font-medium mb-1"
            htmlFor="description"
          >
            Description *
          </label>
          <Textarea
            id="description"
            rows={3}
            {...register("description", {
              required: "Description is required",
            })}
          />
          {errors.description && (
            <p className="text-red-500 text-sm mt-1">
              {errors.description.message}
            </p>
          )}
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label
              className="block text-sm font-medium mb-1"
              htmlFor="prospectPrice"
            >
              Prospect Price
            </label>
            <Input
              id="prospectPrice"
              min="0"
              step="0.01"
              type="number"
              {...register("prospectPrice", {
                valueAsNumber: true,
                min: { value: 0, message: "Price must be positive" },
              })}
            />
            {errors.prospectPrice && (
              <p className="text-red-500 text-sm mt-1">
                {errors.prospectPrice.message}
              </p>
            )}
          </div>

          <div>
            <label
              className="block text-sm font-medium mb-1"
              htmlFor="customerPrice"
            >
              Customer Price
            </label>
            <Input
              id="customerPrice"
              min="0"
              step="0.01"
              type="number"
              {...register("customerPrice", {
                valueAsNumber: true,
                min: { value: 0, message: "Price must be positive" },
              })}
            />
            {errors.customerPrice && (
              <p className="text-red-500 text-sm mt-1">
                {errors.customerPrice.message}
              </p>
            )}
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium mb-1" htmlFor="category">
            Category
          </label>
          <select
            className="px-3 py-2 w-full border border-gray-300 rounded-md"
            id="category"
            {...register("category")}
          >
            <option value="">Select category</option>
            <option value="letters">Letters</option>
            <option value="postcards">Postcards</option>
            <option value="misc">Misc</option>
          </select>
        </div>
      </div>

      <div className="flex justify-end space-x-2">
        <Button
          disabled={isLoading}
          onClick={onCancel}
          type="button"
          variant="outline"
        >
          Cancel
        </Button>
        <Button disabled={isLoading} type="submit">
          {isLoading ? "Saving..." : isEditing ? "Update" : "Create"}
        </Button>
      </div>
    </form>
  );
};

export default CampaignProductForm;
