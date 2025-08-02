"use client";

import { Separator } from "@/components/ui/separator";

interface PriceSummaryProps {
  price: number;
  attendeeCount: number;
}

export function PriceSummary({
  price,
  attendeeCount,
}: PriceSummaryProps): React.ReactElement {
  return (
    <div className="bg-muted p-4 rounded-lg">
      <h4 className="font-semibold mb-2">Price Summary</h4>
      <div className="flex justify-between text-sm">
        <span>Price per attendee:</span>
        <span>${price.toFixed(2)}</span>
      </div>
      <div className="flex justify-between text-sm">
        <span>Number of attendees:</span>
        <span>{attendeeCount}</span>
      </div>
      <Separator className="my-2" />
      <div className="flex justify-between font-semibold">
        <span>Total:</span>
        <span>${(price * attendeeCount).toFixed(2)}</span>
      </div>
    </div>
  );
}
