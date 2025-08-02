import React, { useState } from "react";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { useToast } from "@/components/ui/use-toast";
import { Check, Loader2, Ticket, X } from "lucide-react";

interface VoucherRedemptionProps {
  availableVoucherSeats: number;
  attendeeCount: number;
  onVoucherApplied: (registrationsCovered: number) => void;
  onVoucherRemoved: () => void;
  isVoucherApplied: boolean;
}

export default function VoucherRedemption({
  availableVoucherSeats,
  attendeeCount,
  isVoucherApplied,
  onVoucherApplied,
  onVoucherRemoved,
}: VoucherRedemptionProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [voucherQuantity, setVoucherQuantity] = useState<number>(1);
  const { toast } = useToast();

  const handleRedeemVoucher = () => {
    setIsLoading(true);
    if (voucherQuantity > availableVoucherSeats) {
      setError("Not enough voucher seats available.");
      setIsLoading(false);
      return;
    }
    onVoucherApplied(voucherQuantity);
    setIsLoading(false);
    toast({
      title: "Voucher applied!",
      description: `${voucherQuantity} voucher seat(s) applied.`,
    });
  };

  const handleRemoveVoucher = () => {
    onVoucherRemoved();
  };

  if (isVoucherApplied) {
    return (
      <div className="mb-4">
        <Alert className="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
          <div className="flex items-center">
            <Check className="h-4 w-4 text-green-500 mr-2" />
            <AlertDescription className="text-green-700 dark:text-green-300 font-medium">
              Voucher successfully applied
            </AlertDescription>
            <Button
              className="ml-auto text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
              onClick={handleRemoveVoucher}
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
          <Ticket className="h-4 w-4 mr-2 text-primary" />
          Apply Voucher
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          <div className="space-y-2">
            <p className="text-sm font-medium">
              You have {availableVoucherSeats} available voucher
              {availableVoucherSeats !== 1 ? " seats" : " seat"}
            </p>
            <p className="text-xs text-muted-foreground">
              Each voucher seat covers 1 registration.
            </p>
          </div>

          <div className="space-y-2">
            <Label className="text-sm font-medium" htmlFor="voucher-quantity">
              Number of voucher seats to apply
            </Label>
            <div className="flex items-center gap-2">
              <Input
                className="w-24"
                disabled={isLoading || availableVoucherSeats === 0}
                id="voucher-quantity"
                max={Math.min(availableVoucherSeats, attendeeCount)}
                min={1}
                onChange={(e) =>
                  setVoucherQuantity(
                    Math.max(
                      1,
                      Math.min(
                        parseInt(e.target.value) || 1,
                        Math.min(availableVoucherSeats, attendeeCount),
                      ),
                    ),
                  )
                }
                type="number"
                value={voucherQuantity}
              />
              <span className="text-sm text-muted-foreground">
                (Max: {Math.min(availableVoucherSeats, attendeeCount)})
              </span>
              <Button
                className="ml-auto"
                disabled={isLoading || availableVoucherSeats === 0}
                onClick={handleRedeemVoucher}
              >
                {isLoading ? (
                  <>
                    <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                    Applying...
                  </>
                ) : (
                  "Apply Voucher"
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
