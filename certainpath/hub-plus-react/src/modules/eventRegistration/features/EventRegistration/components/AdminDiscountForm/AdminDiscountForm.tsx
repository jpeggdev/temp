import React, { useState } from "react";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Check, X, Tag } from "lucide-react";
import { useToast } from "@/components/ui/use-toast";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

type DiscountData = {
  discount_type: "percentage" | "fixed_amount";
  discount_value: number;
  reason: string;
};

interface AdminDiscountFormProps {
  baseAmount: number;
  onDiscountApplied: (
    discountAmount: number,
    discountData: DiscountData,
  ) => void;
  onDiscountRemoved: () => void;
  isDiscountApplied: boolean;
}

export default function AdminDiscountForm({
  baseAmount,
  onDiscountApplied,
  onDiscountRemoved,
  isDiscountApplied,
}: AdminDiscountFormProps) {
  const [discountType, setDiscountType] = useState<
    "percentage" | "fixed_amount"
  >("percentage");
  const [discountValue, setDiscountValue] = useState<string>("10");
  const [discountReason, setDiscountReason] = useState<string>("");
  const [error, setError] = useState<string | null>(null);

  const { toast } = useToast();

  const handleApplyDiscount = () => {
    if (!discountReason.trim()) {
      setError("Discount reason is required.");
      return;
    }
    const parsedVal = parseFloat(discountValue) || 0;
    if (parsedVal <= 0) {
      setError("Discount amount or percentage must be greater than 0.");
      return;
    }

    let newDiscountAmount = 0;

    if (discountType === "percentage") {
      const fraction = parsedVal / 100;
      newDiscountAmount = baseAmount * fraction;
    } else {
      newDiscountAmount = parsedVal;
    }

    if (newDiscountAmount <= 0) {
      setError("Nothing to discount. Possibly the base amount is zero?");
      return;
    }

    newDiscountAmount = Math.min(newDiscountAmount, baseAmount);

    onDiscountApplied(newDiscountAmount, {
      discount_type: discountType,
      discount_value: parsedVal,
      reason: discountReason,
    });
    setError(null);

    toast({
      title: "Admin Discount Applied",
      description:
        discountType === "percentage"
          ? `${discountValue}% off. Reason: ${discountReason}`
          : `$${discountValue} off. Reason: ${discountReason}`,
    });
  };

  const handleRemoveDiscount = () => {
    onDiscountRemoved();
  };

  if (isDiscountApplied) {
    return (
      <div className="mb-4">
        <Alert className="bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800">
          <div className="flex items-center">
            <Check className="h-4 w-4 text-purple-500 mr-2" />
            <AlertDescription className="text-purple-700 dark:text-purple-300 font-medium">
              Admin discount is currently applied.
            </AlertDescription>
            <Button
              className="ml-auto text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
              onClick={handleRemoveDiscount}
              size="sm"
              variant="ghost"
            >
              <X className="h-4 w-4 mr-1" />
              Remove
            </Button>
          </div>
        </Alert>
      </div>
    );
  }

  return (
    <Card className="mb-4">
      <CardHeader className="pb-3">
        <CardTitle className="text-base flex items-center">
          <Tag className="h-4 w-4 mr-2 text-primary" />
          Apply Admin Discount
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          <div className="flex space-x-4">
            <label className="flex items-center space-x-1">
              <input
                checked={discountType === "percentage"}
                name="discount-type"
                onChange={() => setDiscountType("percentage")}
                type="radio"
                value="percentage"
              />
              <span>Percentage</span>
            </label>
            <label className="flex items-center space-x-1">
              <input
                checked={discountType === "fixed_amount"}
                name="discount-type"
                onChange={() => setDiscountType("fixed_amount")}
                type="radio"
                value="fixed_amount"
              />
              <span>Fixed Amount</span>
            </label>
          </div>

          <div className="space-y-2">
            <Label htmlFor="discount-value">
              {discountType === "percentage" ? "Percentage" : "Amount"}
            </Label>
            <Input
              id="discount-value"
              onChange={(e) => setDiscountValue(e.target.value)}
              placeholder={discountType === "percentage" ? "10" : "25.00"}
              type="number"
              value={discountValue}
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="discount-reason">Reason</Label>
            <textarea
              className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
              id="discount-reason"
              onChange={(e) => setDiscountReason(e.target.value)}
              placeholder="Enter reason for discount (required)"
              value={discountReason}
            />
          </div>

          {error && <p className="text-sm text-red-500 mt-1">{error}</p>}

          <div className="pt-2">
            <button
              className="w-full bg-primary text-white px-4 py-2 rounded hover:opacity-90"
              onClick={handleApplyDiscount}
            >
              Apply Admin Discount
            </button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
