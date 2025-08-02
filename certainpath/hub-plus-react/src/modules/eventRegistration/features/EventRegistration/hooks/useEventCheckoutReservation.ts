import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useDispatch } from "react-redux";
import {
  createEventCheckoutSessionAction,
  getEventCheckoutSessionDetailsAction,
  setEventCheckoutSessionDetails,
} from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutSlice";
import { resetEventCheckoutSessionReservationExpirationAction } from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutEntrySlice";
import { GetEventCheckoutSessionDetailsResponseData } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/types";
import { ResetEventCheckoutSessionReservationExpirationResponseData } from "@/modules/eventRegistration/features/EventRegistration/api/resetEventCheckoutSessionReservationExpiration/types";
import { CreateEventCheckoutSessionResponseData } from "@/modules/eventRegistration/features/EventRegistration/api/createEventCheckoutSession/types";

interface UseEventReservationSessionProps {
  existingCheckoutSessionUuid?: string | null;
  reservationExpiresAt?: string | null;
  eventSessionUuid?: string | null;
  getDetailsData: GetEventCheckoutSessionDetailsResponseData | null;
}

interface UseEventReservationSessionReturn {
  timeLeft: number | null;
  showSessionModal: boolean;
  isContinuing: boolean;
  handleContinueSession: () => Promise<void>;
  handleStartNewSession: () => Promise<void>;
  formatTimeLeft: (ms: number) => string;
}

export function useEventCheckoutReservation(
  props: UseEventReservationSessionProps,
): UseEventReservationSessionReturn {
  const {
    existingCheckoutSessionUuid,
    reservationExpiresAt,
    eventSessionUuid,
    getDetailsData,
  } = props;

  const dispatch = useDispatch();
  const navigate = useNavigate();

  const [timeLeft, setTimeLeft] = useState<number | null>(null);
  const [showSessionModal, setShowSessionModal] = useState(false);
  const [isContinuing, setIsContinuing] = useState(false);

  useEffect(() => {
    if (!reservationExpiresAt || !existingCheckoutSessionUuid) {
      return;
    }

    function updateCountdown() {
      const now = Date.now();
      const expiryTime = new Date(reservationExpiresAt || "").getTime();
      const difference = expiryTime - now;
      if (difference <= 0) {
        setTimeLeft(0);
        setShowSessionModal(true);
      } else {
        setTimeLeft(difference);
      }
    }

    updateCountdown();
    const intervalId = setInterval(updateCountdown, 1000);
    return () => clearInterval(intervalId);
  }, [reservationExpiresAt, existingCheckoutSessionUuid]);

  function formatTimeLeft(ms: number): string {
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return `${minutes}:${seconds.toString().padStart(2, "0")}`;
  }

  async function handleContinueSession() {
    setIsContinuing(true);
    if (!existingCheckoutSessionUuid) {
      setIsContinuing(false);
      return;
    }
    dispatch(
      resetEventCheckoutSessionReservationExpirationAction(
        { eventCheckoutSessionUuid: existingCheckoutSessionUuid },
        (
          response: ResetEventCheckoutSessionReservationExpirationResponseData,
        ) => {
          const newExpiresAt = response.reservationExpiresAt;
          const difference = new Date(newExpiresAt).getTime() - Date.now();
          setTimeLeft(difference > 0 ? difference : 0);
          if (getDetailsData) {
            dispatch(
              setEventCheckoutSessionDetails({
                ...getDetailsData,
                reservationExpiresAt: newExpiresAt,
              }),
            );
          }

          dispatch(
            getEventCheckoutSessionDetailsAction(
              existingCheckoutSessionUuid,
              () => {
                setShowSessionModal(false);
                setIsContinuing(false);
              },
            ),
          );
        },
      ),
    );
  }

  async function handleStartNewSession() {
    if (!eventSessionUuid) {
      setShowSessionModal(false);
      return;
    }
    dispatch(
      createEventCheckoutSessionAction(
        { eventSessionUuid },
        (created: CreateEventCheckoutSessionResponseData) => {
          if (created?.uuid) {
            navigate(
              `/event-registration/events/register/${created.uuid}/attendees`,
            );
            setShowSessionModal(false);
          }
        },
      ),
    );
  }

  return {
    timeLeft,
    showSessionModal,
    isContinuing,
    handleContinueSession,
    handleStartNewSession,
    formatTimeLeft,
  };
}
