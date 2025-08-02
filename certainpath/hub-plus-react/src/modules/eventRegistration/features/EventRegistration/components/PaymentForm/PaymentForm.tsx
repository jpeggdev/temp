import React, { useState } from "react";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import { CreditCard, Lock, Loader2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { processPayment } from "@/modules/eventRegistration/features/EventRegistration/api/processPayment/processPaymentApi";
import errorCodeToMessage from "@/modules/eventRegistration/features/EventRegistration/utils/errorMappings";

interface SecureData {
  authData: {
    clientKey: string;
    apiLoginID: string;
  };
  cardData: {
    cardNumber: string;
    month: string;
    year: string;
    cardCode: string;
    fullName: string;
  };
}

declare global {
  interface Window {
    Accept?: {
      dispatchData: (
        secureData: SecureData,
        callback: (response: AcceptResponse) => void,
      ) => void;
    };
  }
}

interface AcceptResponse {
  messages: {
    resultCode: string;
    message: Array<{
      code: string;
      text: string;
    }>;
  };
  opaqueData: {
    dataDescriptor: string;
    dataValue: string;
  };
}

type AdminDiscountType = "percentage" | "fixed_amount" | null;

interface PaymentFormProps {
  total?: number;
  isProcessing?: boolean;
  setIsProcessing?: (value: boolean) => void;
  handlePaymentSuccess?: () => void;
  voucherQuantity?: number;
  discountCode?: string | null;
  discountAmount?: number;
  adminDiscountType?: AdminDiscountType;
  adminDiscountValue?: number;
  adminDiscountReason?: string;
  eventCheckoutSessionUuid?: string;
}

interface ApiError {
  response?: {
    data?: {
      errorCode?: string;
      message?: string;
    };
  };
}

function formatCardNumber(value: string): string {
  let numericValue = value.replace(/\D/g, "");
  numericValue = numericValue.slice(0, 16);
  const chunks = numericValue.match(/.{1,4}/g);
  return chunks ? chunks.join(" ") : numericValue;
}

function generateUniqueInvoiceNumber(): string {
  const timestamp = Date.now().toString().slice(-8);
  const random = Math.floor(Math.random() * 1000)
    .toString()
    .padStart(3, "0");
  return `INV${timestamp}${random}`;
}

function PaymentForm({
  total = 0,
  isProcessing = false,
  setIsProcessing = () => {},
  handlePaymentSuccess = () => {},
  voucherQuantity = 0,
  discountCode = null,
  discountAmount = 0,
  adminDiscountType = null,
  adminDiscountValue = 0,
  adminDiscountReason = "",
  eventCheckoutSessionUuid,
}: PaymentFormProps) {
  const [cardholderName, setCardholderName] = useState("");
  const [cardNumber, setCardNumber] = useState("");
  const [expiryMonth, setExpiryMonth] = useState("");
  const [expiryYear, setExpiryYear] = useState("");
  const [cardCode, setCardCode] = useState("");
  const [saveOnFile] = useState(false);
  const [invoiceNumber, setInvoiceNumber] = useState(
    generateUniqueInvoiceNumber(),
  );
  const [errorMessage, setErrorMessage] = useState("");

  const apiLoginID = process.env.REACT_APP_AUTHNET_API_LOGIN_ID || "";
  const clientKey = process.env.REACT_APP_AUTHNET_CLIENT_KEY || "";

  const regenerateInvoiceNumber = () => {
    setInvoiceNumber(generateUniqueInvoiceNumber());
  };

  const handleAcceptResponse = async (response: AcceptResponse) => {
    if (response.messages.resultCode === "Error") {
      if (
        Array.isArray(response.messages.message) &&
        response.messages.message.length
      ) {
        const userMessages = response.messages.message.map((msg) => {
          const friendlyMessage = errorCodeToMessage[msg.code] || msg.text;
          return friendlyMessage;
        });
        setErrorMessage(userMessages.join("\n"));
      }
      setIsProcessing(false);
      return;
    }
    setErrorMessage("");
    const { dataDescriptor, dataValue } = response.opaqueData;
    try {
      await processPayment({
        dataDescriptor,
        dataValue,
        amount: total,
        shouldCreatePaymentProfile: saveOnFile,
        invoiceNumber,
        voucherQuantity,
        discountCode: discountCode || undefined,
        discountAmount,
        adminDiscountType: adminDiscountType || undefined,
        adminDiscountValue,
        adminDiscountReason,
        eventCheckoutSessionUuid,
      });
      setIsProcessing(false);
      handlePaymentSuccess();
    } catch (error: unknown) {
      setIsProcessing(false);
      const apiError = error as ApiError;
      const errorMsg = apiError.response?.data?.message;
      setErrorMessage(
        errorMsg ||
          "An error occurred while processing your payment. Please try again.",
      );
      regenerateInvoiceNumber();
    }
  };

  const handlePayment = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsProcessing(true);
    regenerateInvoiceNumber();
    const secureData: SecureData = {
      authData: {
        clientKey,
        apiLoginID,
      },
      cardData: {
        cardNumber: cardNumber.replace(/\s+/g, ""),
        month: expiryMonth,
        year: expiryYear,
        cardCode,
        fullName: cardholderName,
      },
    };
    if (window.Accept && typeof window.Accept.dispatchData === "function") {
      window.Accept.dispatchData(secureData, handleAcceptResponse);
    } else {
      setIsProcessing(false);
      setErrorMessage(
        "Payment functionality is currently unavailable. Please contact support.",
      );
    }
  };

  const handleCardNumberChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setCardNumber(formatCardNumber(e.target.value));
  };

  const handleExpiryMonthChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let value = e.target.value.replace(/\D/g, "");
    value = value.slice(0, 2);
    setExpiryMonth(value);
  };

  const handleExpiryYearChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let value = e.target.value.replace(/\D/g, "");
    value = value.slice(0, 2);
    setExpiryYear(value);
  };

  const handleCardCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let value = e.target.value.replace(/\D/g, "");
    value = value.slice(0, 4);
    setCardCode(value);
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-base font-medium flex items-center">
          <CreditCard className="h-4 w-4 mr-2 text-primary" />
          Payment Information
        </CardTitle>
        <CardDescription>
          Enter your payment details to complete registration
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="border rounded p-4">
          <div className="mb-4">
            <h2 className="text-lg font-semibold flex items-center">
              <CreditCard className="h-5 w-5 mr-2 text-primary" />
              Payment Information
            </h2>
          </div>
          {errorMessage && (
            <div className="mb-4 p-2 bg-red-50 border border-red-300 text-red-600 rounded">
              {errorMessage}
            </div>
          )}
          <form className="space-y-4" onSubmit={handlePayment}>
            <div className="space-y-2">
              <label
                className="text-sm font-medium block"
                htmlFor="cardholderName"
              >
                Cardholder Name
              </label>
              <input
                className="w-full border p-2 rounded"
                disabled={isProcessing}
                id="cardholderName"
                onChange={(e) => setCardholderName(e.target.value)}
                placeholder="John Doe"
                required
                type="text"
                value={cardholderName}
              />
            </div>
            <div className="space-y-2">
              <label className="text-sm font-medium block" htmlFor="cardNumber">
                Card Number
              </label>
              <input
                className="w-full border p-2 rounded"
                disabled={isProcessing}
                id="cardNumber"
                onChange={handleCardNumberChange}
                placeholder="4111 1111 1111 1111"
                required
                type="text"
                value={cardNumber}
              />
            </div>
            <div className="grid grid-cols-3 gap-4">
              <div className="space-y-2">
                <label
                  className="text-sm font-medium block"
                  htmlFor="expiryMonth"
                >
                  Month
                </label>
                <input
                  className="w-full border p-2 rounded"
                  disabled={isProcessing}
                  id="expiryMonth"
                  onChange={handleExpiryMonthChange}
                  placeholder="MM"
                  required
                  type="text"
                  value={expiryMonth}
                />
              </div>
              <div className="space-y-2">
                <label
                  className="text-sm font-medium block"
                  htmlFor="expiryYear"
                >
                  Year
                </label>
                <input
                  className="w-full border p-2 rounded"
                  disabled={isProcessing}
                  id="expiryYear"
                  onChange={handleExpiryYearChange}
                  placeholder="YY"
                  required
                  type="text"
                  value={expiryYear}
                />
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium block" htmlFor="cardCode">
                  CVV
                </label>
                <input
                  className="w-full border p-2 rounded"
                  disabled={isProcessing}
                  id="cardCode"
                  onChange={handleCardCodeChange}
                  placeholder="123"
                  required
                  type="text"
                  value={cardCode}
                />
              </div>
            </div>
            <Button className="w-full" disabled={isProcessing} type="submit">
              {isProcessing ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Processing...
                </>
              ) : (
                <>
                  <CreditCard className="h-4 w-4 mr-2" />
                  Complete Registration (${total.toFixed(2)})
                </>
              )}
            </Button>
            <div className="text-xs text-center text-gray-500 mt-2">
              <div className="flex items-center justify-center mb-1">
                <Lock className="h-3 w-3 mr-1" />
                <span>Secure Payment</span>
              </div>
              <p>
                Your payment information is encrypted and processed securely.
              </p>
            </div>
          </form>
        </div>
      </CardContent>
    </Card>
  );
}

export default PaymentForm;
