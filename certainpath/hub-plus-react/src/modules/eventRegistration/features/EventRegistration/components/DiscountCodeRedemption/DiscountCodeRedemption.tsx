import React, { useState } from "react";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { useToast } from "@/components/ui/use-toast";
import { Tag, Check, Loader2, X } from "lucide-react";
import { EventCheckoutSessionDiscount } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/types";

interface DiscountCodeRedemptionProps {
  // Instead of using leftover "totalAmount," we now pass the original base amount (subtotal).
  baseAmount: number;
  allAvailableDiscounts: EventCheckoutSessionDiscount[];
  onDiscountApplied: (
    discountAmount: number,
    discountData: { code: string },
  ) => void;
  onDiscountRemoved: () => void;
  isDiscountApplied: boolean;
}

export default function DiscountCodeRedemption({
  baseAmount,
  isDiscountApplied,
  onDiscountApplied,
  onDiscountRemoved,
  allAvailableDiscounts,
}: DiscountCodeRedemptionProps) {
  const { toast } = useToast();

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [discountCode, setDiscountCode] = useState<string>("");
  const [appliedCode, setAppliedCode] = useState<string>("");
  const [discountAmount, setDiscountAmount] = useState<number>(0);

  const handleApplyDiscount = () => {
    if (!discountCode.trim()) return;

    setIsLoading(true);
    setError(null);

    const matchedDiscount = allAvailableDiscounts.find(
      (d) => d.code?.toLowerCase() === discountCode.trim().toLowerCase(),
    );

    if (!matchedDiscount) {
      setError("Invalid or inapplicable discount code.");
      setIsLoading(false);
      return;
    }

    const discountVal = parseFloat(matchedDiscount.discountValue ?? "0") || 0;
    let computedDiscount = 0;

    if (matchedDiscount.discountType === "percentage") {
      computedDiscount = baseAmount * (discountVal / 100);
    } else if (matchedDiscount.discountType === "fixed_amount") {
      computedDiscount = Math.min(discountVal, baseAmount);
    }

    if (computedDiscount <= 0) {
      setError("Discount has no effect on the current base amount.");
      setIsLoading(false);
      return;
    }

    setDiscountAmount(computedDiscount);
    setAppliedCode(discountCode);

    onDiscountApplied(computedDiscount, {
      code: matchedDiscount.code ?? "",
    });

    toast({
      title: "Discount applied!",
      description: `Code "${matchedDiscount.code}" gave you $${computedDiscount.toFixed(
        2,
      )} off!`,
    });

    setIsLoading(false);
  };

  const handleRemoveDiscount = () => {
    setAppliedCode("");
    setDiscountAmount(0);
    onDiscountRemoved();
  };

  if (isDiscountApplied) {
    return (
      <div className="mb-4">
        <Alert className="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
          <div className="flex items-center">
            <Check className="h-4 w-4 text-blue-500 mr-2" />
            <AlertDescription className="text-blue-700 dark:text-blue-300 font-medium">
              Discount code &quot;{appliedCode}&quot; applied: $
              {discountAmount.toFixed(2)} off
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
      <CardHeader className="pb-2">
        <CardTitle className="text-base flex items-center">
          <Tag className="h-4 w-4 mr-2 text-primary" />
          Apply Discount Code
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          <div className="space-y-2">
            <Label className="text-sm font-medium" htmlFor="discount-code">
              Enter discount code
            </Label>
            <div className="flex items-center gap-2">
              <Input
                className="flex-1"
                disabled={isLoading}
                id="discount-code"
                onChange={(e) => setDiscountCode(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter") {
                    e.preventDefault();
                    handleApplyDiscount();
                  }
                }}
                placeholder="PROMO123"
                type="text"
                value={discountCode}
              />
              <Button
                disabled={isLoading || !discountCode.trim()}
                onClick={handleApplyDiscount}
              >
                {isLoading ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  "Apply"
                )}
              </Button>
            </div>
          </div>

          {error && (
            <Alert variant="destructive">
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
