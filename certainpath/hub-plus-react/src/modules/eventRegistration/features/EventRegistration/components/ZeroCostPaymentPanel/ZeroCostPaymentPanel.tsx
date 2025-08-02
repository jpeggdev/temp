import React from "react";
import { Card, CardContent } from "@/components/ui/card";
import { Loader2, Check } from "lucide-react";
import { Button } from "@/components/ui/button";

interface ZeroCostPaymentPanelProps {
  isProcessing: boolean;
  isVoucherApplied: boolean;
  isDiscountApplied: boolean;
  isAdminDiscountApplied: boolean;
  onCompleteRegistration: () => void;
}

export function ZeroCostPaymentPanel({
  isProcessing,
  isVoucherApplied,
  isDiscountApplied,
  isAdminDiscountApplied,
  onCompleteRegistration,
}: ZeroCostPaymentPanelProps) {
  return (
    <Card>
      <CardContent className="pt-6">
        <div className="bg-green-50 dark:bg-green-900/20 p-4 rounded-md">
          <div className="flex items-center text-green-700 dark:text-green-300 font-medium mb-2">
            <Check className="h-5 w-5 mr-2" />
            {isVoucherApplied || isDiscountApplied || isAdminDiscountApplied
              ? "No Payment Required - Fully Covered"
              : "No Payment Required"}
          </div>
          <p className="text-sm text-muted-foreground">
            {isVoucherApplied
              ? "Your voucher covers part or all of the registration cost."
              : isDiscountApplied
                ? "Your discount code covers part or all of the registration cost."
                : isAdminDiscountApplied
                  ? "The admin discount covers part or all of the registration cost."
                  : "Your registration is free of charge."}
          </p>
          <Button
            className="w-full mt-4"
            disabled={isProcessing}
            onClick={onCompleteRegistration}
          >
            {isProcessing ? (
              <>
                <Loader2 className="h-4 w-4 animate-spin mr-2" />
                Processing...
              </>
            ) : (
              "Complete Registration"
            )}
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}

export default ZeroCostPaymentPanel;
