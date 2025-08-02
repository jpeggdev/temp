import React, { useEffect, useState } from "react";
import { useSelector } from "react-redux";
import { useParams, useNavigate } from "react-router-dom";
import { useAppDispatch } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { getEventCheckoutSessionDetailsAction } from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutSlice";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Button } from "@/components/ui/button";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import { ArrowLeft } from "lucide-react";
import EventDetailsHeader from "@/modules/eventRegistration/features/EventRegistration/components/EventDetailsHeader/EventDetailsHeader";
import VoucherRedemption from "@/modules/eventRegistration/features/EventRegistration/components/VoucherRedemption/VoucherRedemption";
import DiscountCodeRedemption from "@/modules/eventRegistration/features/EventRegistration/components/DiscountCodeRedemption/DiscountCodeRedemption";
import AdminDiscountForm from "@/modules/eventRegistration/features/EventRegistration/components/AdminDiscountForm/AdminDiscountForm";
import OrderSummary from "@/modules/eventRegistration/features/EventRegistration/components/OrderSummary/OrderSummary";
import CheckoutLoadingSkeleton from "@/modules/eventRegistration/features/EventRegistration/components/CheckoutLoadingSkeleton/CheckoutLoadingSkeleton";
import { useEventCheckoutReservation } from "@/modules/eventRegistration/features/EventRegistration/hooks/useEventCheckoutReservation";
import { ContinueSessionModal } from "@/modules/eventRegistration/features/EventRegistration/components/ContinueSessionModal /ContinueSessionModal";
import { processPayment } from "@/modules/eventRegistration/features/EventRegistration/api/processPayment/processPaymentApi";
import ZeroCostPaymentPanel from "@/modules/eventRegistration/features/EventRegistration/components/ZeroCostPaymentPanel/ZeroCostPaymentPanel";
import PaymentForm from "@/modules/eventRegistration/features/EventRegistration/components/PaymentForm/PaymentForm";
import type { EventCheckoutSessionAttendee } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/types";

function Checkout() {
  const { eventCheckoutSessionUuid } = useParams<{
    eventCheckoutSessionUuid: string;
  }>();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();

  const [isProcessing, setIsProcessing] = useState(false);
  const [paymentProcessed, setPaymentProcessed] = useState(false);
  const [isVoucherApplied, setIsVoucherApplied] = useState(false);
  const [appliedVoucherQuantity, setAppliedVoucherQuantity] =
    useState<number>(0);
  const [isDiscountApplied, setIsDiscountApplied] = useState(false);
  const [appliedDiscountAmount, setAppliedDiscountAmount] = useState<number>(0);
  const [appliedDiscountCode, setAppliedDiscountCode] = useState<string | null>(
    null,
  );
  const [isAdminDiscountApplied, setIsAdminDiscountApplied] = useState(false);
  const [adminDiscountAmount, setAdminDiscountAmount] = useState<number>(0);
  const [adminDiscountType, setAdminDiscountType] = useState<
    "percentage" | "fixed_amount" | null
  >(null);
  const [adminDiscountValue, setAdminDiscountValue] = useState<number>(0);
  const [adminDiscountReason, setAdminDiscountReason] = useState<string>("");

  const { loadingGetDetails, getDetailsError, eventCheckoutSessionDetails } =
    useSelector((state: RootState) => state.eventCheckout);

  useEffect(() => {
    window.scrollTo(0, 0);
    if (eventCheckoutSessionUuid) {
      dispatch(getEventCheckoutSessionDetailsAction(eventCheckoutSessionUuid));
    }
  }, [dispatch, eventCheckoutSessionUuid]);

  const {
    timeLeft,
    showSessionModal,
    isContinuing,
    handleContinueSession,
    handleStartNewSession,
    formatTimeLeft,
  } = useEventCheckoutReservation({
    existingCheckoutSessionUuid: eventCheckoutSessionUuid,
    reservationExpiresAt:
      eventCheckoutSessionDetails?.reservationExpiresAt ?? null,
    eventSessionUuid: eventCheckoutSessionDetails?.eventSessionUuid ?? null,
    getDetailsData: eventCheckoutSessionDetails,
  });

  const paidAttendees: EventCheckoutSessionAttendee[] =
    eventCheckoutSessionDetails?.attendees?.filter(
      (a) => a.isSelected && !a.isWaitlist,
    ) ?? [];

  const waitlistedAttendees: EventCheckoutSessionAttendee[] =
    eventCheckoutSessionDetails?.attendees?.filter(
      (a) => a.isSelected && a.isWaitlist,
    ) ?? [];

  const paidAttendeeCount = paidAttendees.length;
  const waitlistedAttendeeCount = waitlistedAttendees.length;
  const userHeldSeats = paidAttendeeCount;
  const eventName = eventCheckoutSessionDetails?.eventName ?? "Untitled Event";
  const eventPrice = eventCheckoutSessionDetails?.eventPrice ?? 0;
  const availableSeats = eventCheckoutSessionDetails?.availableSeats ?? 0;
  const availableVoucherSeats =
    eventCheckoutSessionDetails?.companyAvailableVoucherSeats || 0;
  const occupiedAttendeeSeatsByCurrentUser =
    eventCheckoutSessionDetails?.occupiedAttendeeSeatsByCurrentUser ??
    userHeldSeats;

  const baseAmount = paidAttendeeCount * eventPrice;
  const voucherCoverage = appliedVoucherQuantity * eventPrice;
  const discountCoverage = appliedDiscountAmount;
  const adminCoverage = adminDiscountAmount;
  const total = Math.max(
    0,
    baseAmount - voucherCoverage - discountCoverage - adminCoverage,
  );

  function handlePaymentSuccess() {
    setIsProcessing(false);
    setPaymentProcessed(true);
    if (eventCheckoutSessionUuid) {
      navigate(
        `/event-registration/events/register/${eventCheckoutSessionUuid}/confirmation`,
      );
    }
  }

  async function handleCompleteRegistration() {
    if (!eventCheckoutSessionUuid) return;
    setIsProcessing(true);
    try {
      const invoiceNumber = `INV${Date.now()}`;
      await processPayment({
        dataDescriptor: "FREE",
        dataValue: "FREE",
        amount: 0,
        shouldCreatePaymentProfile: false,
        invoiceNumber,
        voucherQuantity: appliedVoucherQuantity,
        discountCode: appliedDiscountCode || undefined,
        discountAmount: appliedDiscountAmount,
        adminDiscountType: adminDiscountType || undefined,
        adminDiscountValue,
        adminDiscountReason,
        eventCheckoutSessionUuid,
      });
      setIsProcessing(false);
      setPaymentProcessed(true);
      navigate(
        `/event-registration/events/register/${eventCheckoutSessionUuid}/confirmation`,
      );
    } catch {
      setIsProcessing(false);
    }
  }

  function handleBackToRegistration() {
    navigate(-1);
  }

  if (loadingGetDetails) {
    return <CheckoutLoadingSkeleton />;
  }

  const hasWaitlistAttendees = waitlistedAttendeeCount > 0;
  const shouldShowPaymentForm =
    !paymentProcessed && (total > 0 || hasWaitlistAttendees);
  const shouldShowZeroCost =
    !paymentProcessed && total === 0 && !hasWaitlistAttendees;

  return (
    <>
      <MainPageWrapper hideHeader title="Checkout">
        <div className="mb-6">
          <Button
            className="px-0 flex items-center gap-2 text-muted-foreground hover:text-primary"
            onClick={handleBackToRegistration}
            variant="link"
          >
            <ArrowLeft className="mr-1 h-4 w-4" />
            Back to Registration
          </Button>
        </div>
        {getDetailsError && (
          <Alert className="mb-4" variant="destructive">
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{getDetailsError}</AlertDescription>
          </Alert>
        )}
        {eventCheckoutSessionDetails && (
          <EventDetailsHeader
            eventData={{ title: eventName, accepts_vouchers: true }}
            sessionData={{
              title: eventCheckoutSessionDetails.eventSessionName,
              start_time: eventCheckoutSessionDetails.startDate,
              end_time: eventCheckoutSessionDetails.endDate,
              timezoneIdentifier:
                eventCheckoutSessionDetails.timezoneIdentifier,
              timezoneShortName: eventCheckoutSessionDetails.timezoneShortName,
            }}
          />
        )}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-1">
            <OrderSummary
              adminDiscountAmount={adminDiscountAmount}
              allAttendees={eventCheckoutSessionDetails?.attendees || []}
              availableSeats={availableSeats}
              checkoutSessionUuid={eventCheckoutSessionUuid || ""}
              codeDiscountAmount={appliedDiscountAmount}
              eventName={eventName}
              eventPrice={eventPrice}
              formatTimeLeft={formatTimeLeft}
              occupiedAttendeeSeatsByCurrentUser={
                occupiedAttendeeSeatsByCurrentUser
              }
              paidAttendeeCount={paidAttendeeCount}
              timeLeft={timeLeft}
              total={total}
              userHeldSeats={userHeldSeats}
              voucherSeatsUsed={appliedVoucherQuantity}
              waitlistedAttendeeCount={waitlistedAttendeeCount}
            />
          </div>
          <div className="lg:col-span-2">
            <div className="space-y-6">
              {availableVoucherSeats > 0 && (
                <VoucherRedemption
                  attendeeCount={paidAttendeeCount + waitlistedAttendeeCount}
                  availableVoucherSeats={availableVoucherSeats}
                  isVoucherApplied={isVoucherApplied}
                  onVoucherApplied={(quantity: number) => {
                    setIsVoucherApplied(true);
                    setAppliedVoucherQuantity(quantity);
                  }}
                  onVoucherRemoved={() => {
                    setIsVoucherApplied(false);
                    setAppliedVoucherQuantity(0);
                  }}
                />
              )}
              {eventCheckoutSessionDetails?.discounts &&
                eventCheckoutSessionDetails.discounts.length > 0 && (
                  <DiscountCodeRedemption
                    allAvailableDiscounts={
                      eventCheckoutSessionDetails.discounts
                    }
                    baseAmount={baseAmount}
                    isDiscountApplied={isDiscountApplied}
                    onDiscountApplied={(discountAmount, discountData) => {
                      setIsDiscountApplied(true);
                      setAppliedDiscountAmount(discountAmount);
                      setAppliedDiscountCode(discountData.code);
                    }}
                    onDiscountRemoved={() => {
                      setIsDiscountApplied(false);
                      setAppliedDiscountAmount(0);
                      setAppliedDiscountCode(null);
                    }}
                  />
                )}
              <ShowIfHasAccess requiredRoles={["ROLE_SUPER_ADMIN"]}>
                <AdminDiscountForm
                  baseAmount={baseAmount}
                  isDiscountApplied={isAdminDiscountApplied}
                  onDiscountApplied={(newDiscountAmount, discountData) => {
                    setIsAdminDiscountApplied(true);
                    setAdminDiscountAmount(newDiscountAmount);
                    setAdminDiscountType(discountData.discount_type || null);
                    setAdminDiscountValue(discountData.discount_value || 0);
                    setAdminDiscountReason(discountData.reason || "");
                  }}
                  onDiscountRemoved={() => {
                    setIsAdminDiscountApplied(false);
                    setAdminDiscountAmount(0);
                    setAdminDiscountType(null);
                    setAdminDiscountValue(0);
                    setAdminDiscountReason("");
                  }}
                />
              </ShowIfHasAccess>
              {shouldShowPaymentForm && (
                <PaymentForm
                  adminDiscountReason={adminDiscountReason}
                  adminDiscountType={adminDiscountType}
                  adminDiscountValue={adminDiscountValue}
                  discountAmount={appliedDiscountAmount}
                  discountCode={appliedDiscountCode}
                  eventCheckoutSessionUuid={eventCheckoutSessionUuid}
                  handlePaymentSuccess={handlePaymentSuccess}
                  isProcessing={isProcessing}
                  setIsProcessing={setIsProcessing}
                  total={total}
                  voucherQuantity={appliedVoucherQuantity}
                />
              )}
              {shouldShowZeroCost && (
                <ZeroCostPaymentPanel
                  isAdminDiscountApplied={isAdminDiscountApplied}
                  isDiscountApplied={isDiscountApplied}
                  isProcessing={isProcessing}
                  isVoucherApplied={isVoucherApplied}
                  onCompleteRegistration={handleCompleteRegistration}
                />
              )}
              {paymentProcessed && <div />}
            </div>
          </div>
        </div>
      </MainPageWrapper>
      <ContinueSessionModal
        createdAt={eventCheckoutSessionDetails?.reservationExpiresAt ?? null}
        eventName={eventName}
        isContinuing={isContinuing}
        isOpen={showSessionModal}
        onContinue={handleContinueSession}
        onStartNew={handleStartNewSession}
      />
    </>
  );
}

export default Checkout;
