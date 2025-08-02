import { useState, useEffect } from "react";
import { format, parseISO } from "date-fns";
import { useToast } from "@/components/ui/use-toast";
import { useDispatch, useSelector } from "react-redux";
import { AppDispatch } from "@/app/store";
import { RootState } from "@/app/rootReducer";
import {
  clearEnrollmentsWaitlistState,
  fetchEventEnrollmentsAction,
  fetchEventWaitlistItemsAction,
  fetchWaitlistDetailsAction,
  updateWaitlistPositionAction,
  moveWaitlistToEnrollmentAction,
  removeWaitlistItemAction,
  // NEW ACTION: move enrollment -> waitlist
  moveEnrollmentToWaitlistAction,
} from "@/modules/eventRegistration/features/EventWaitlist/slices/enrollmentsWaitlistSlice";
import { EventEnrollmentItemResponseDTO } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventEnrollments/types";
import { EventWaitlistItemResponse } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventWaitlistItems/types";

export type RegisteredUser = EventEnrollmentItemResponseDTO;
export type WaitlistEntry = EventWaitlistItemResponse;

interface UseWaitlistProps {
  eventUuid?: string;
}

export default function useWaitlist({ eventUuid }: UseWaitlistProps = {}) {
  const { toast } = useToast();
  const dispatch = useDispatch<AppDispatch>();

  const { enrollments, waitlistItems, waitlistDetails, errorUpdatePosition } =
    useSelector((state: RootState) => state.enrollmentsWaitlist);

  const [activeTab, setActiveTab] = useState<"waitlist" | "registered">(
    "waitlist",
  );
  const [isProcessing, setIsProcessing] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isMovingToWaitlist, setIsMovingToWaitlist] = useState(false);
  const [isReplacing] = useState(false);
  const [isRegistering, setIsRegistering] = useState(false);
  const [isChangingPosition, setIsChangingPosition] = useState(false);

  const [showRemoveDialog, setShowRemoveDialog] = useState(false);
  const [showMoveToWaitlistDialog, setShowMoveToWaitlistDialog] =
    useState(false);
  const [showReplaceDialog, setShowReplaceDialog] = useState(false);
  const [showRegisterDialog, setShowRegisterDialog] = useState(false);

  const [selectedUser, setSelectedUser] = useState<WaitlistEntry | null>(null);
  const [selectedRegistration, setSelectedRegistration] =
    useState<RegisteredUser | null>(null);

  useEffect(() => {
    if (!eventUuid) return;
    (async () => {
      try {
        await dispatch(fetchEventEnrollmentsAction({ uuid: eventUuid }));
        await dispatch(fetchEventWaitlistItemsAction({ uuid: eventUuid }));
        await dispatch(fetchWaitlistDetailsAction({ uuid: eventUuid }));
      } catch (error) {
        console.error("Error fetching initial data:", error);
        toast({
          title: "Error",
          description: "Failed to fetch event data. Please try again.",
          variant: "destructive",
        });
      }
    })();

    return () => {
      dispatch(clearEnrollmentsWaitlistState());
    };
  }, [eventUuid, dispatch, toast]);

  const handleTabChange = (value: string) => {
    if (value === "waitlist" || value === "registered") {
      setActiveTab(value);
    }
  };

  const refreshData = async () => {
    if (!eventUuid) return;
    setIsRefreshing(true);
    try {
      await dispatch(fetchEventEnrollmentsAction({ uuid: eventUuid }));
      await dispatch(fetchEventWaitlistItemsAction({ uuid: eventUuid }));
      await dispatch(fetchWaitlistDetailsAction({ uuid: eventUuid }));
      toast({
        title: "Data refreshed",
        description: "The waitlist data has been refreshed.",
      });
    } catch (error) {
      console.error("Error refreshing data:", error);
      toast({
        title: "Error",
        description: "Failed to refresh data. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsRefreshing(false);
    }
  };

  const processWaitlist = async () => {
    console.log("processWaitlist called");
  };

  /**
   * Remove a user from waitlist
   */
  const removeFromWaitlist = async (registrationId: number) => {
    if (!eventUuid) return;
    setIsProcessing(true);
    try {
      await dispatch(
        removeWaitlistItemAction({
          uuid: eventUuid,
          eventWaitlistId: registrationId,
        }),
      );
      toast({
        title: "Removed from waitlist",
        description: "User has been removed from the waitlist.",
      });
      // Refresh the data after the removal
      await refreshData();
    } catch (error) {
      console.error("Error removing from waitlist:", error);
      const message =
        error instanceof Error
          ? error.message
          : "Failed to remove waitlist item.";
      toast({
        title: "Error",
        description: message,
        variant: "destructive",
      });
    } finally {
      setIsProcessing(false);
    }
  };

  /**
   * Move a registration to waitlist (not used in your snippet but here as an example)
   */
  const moveToWaitlist = async (registration: RegisteredUser) => {
    console.log("moveToWaitlist called with registration:", registration);
  };

  /**
   * Register a user from the waitlist (move waitlist to enrollment)
   */
  const registerFromWaitlist = async (waitlistId: number) => {
    if (!eventUuid) return;
    setIsRegistering(true);
    try {
      await dispatch(
        moveWaitlistToEnrollmentAction({
          uuid: eventUuid,
          eventWaitlistId: waitlistId,
        }),
      );
      toast({
        title: "User registered",
        description:
          "User has been successfully moved from waitlist to enrollments.",
      });
      // Refresh the data after the move
      await refreshData();
    } catch (error) {
      console.error("Error moving waitlist to enrollment:", error);
      const message =
        error instanceof Error
          ? error.message
          : "Failed to move waitlist to enrollment.";
      toast({
        title: "Error",
        description: message,
        variant: "destructive",
      });
    } finally {
      setIsRegistering(false);
    }
  };

  /**
   * NEW: Move an existing enrollment -> waitlist
   */
  const moveFromEnrollmentToWaitlist = async (enrollmentId: number) => {
    if (!eventUuid) return;
    setIsMovingToWaitlist(true);
    try {
      await dispatch(
        moveEnrollmentToWaitlistAction({
          uuid: eventUuid,
          enrollmentId: enrollmentId,
        }),
      );
      toast({
        title: "Moved to waitlist",
        description: "The user has been moved from enrollment to the waitlist.",
      });
      // Refresh data
      await refreshData();
    } catch (error) {
      console.error("Error moving enrollment to waitlist:", error);
      const message =
        error instanceof Error
          ? error.message
          : "Failed to move enrollment to waitlist.";
      toast({
        title: "Error",
        description: message,
        variant: "destructive",
      });
    } finally {
      setIsMovingToWaitlist(false);
    }
  };

  /**
   * Replace a registration with a new employee
   */
  const replaceEmployee = async (
    registrationId: string,
    newEmployeeId: string,
  ) => {
    console.log(
      "replaceEmployee called - OldReg:",
      registrationId,
      " NewEmp:",
      newEmployeeId,
    );
  };

  /**
   * Update a waitlist item's position optimistically, then refresh or revert on error.
   */
  const updateWaitlistPosition = async (
    waitlistId: number,
    newPosition: number,
  ) => {
    if (!eventUuid) return;

    setIsChangingPosition(true);
    try {
      await dispatch(
        updateWaitlistPositionAction(
          {
            uuid: eventUuid,
            eventWaitlistId: waitlistId,
            newPosition: newPosition,
          },
          () => dispatch(fetchEventWaitlistItemsAction({ uuid: eventUuid })),
        ),
      );

      if (errorUpdatePosition) {
        throw new Error(errorUpdatePosition);
      }
    } catch (error) {
      console.error("Error updating position:", error);
      throw error;
    } finally {
      setIsChangingPosition(false);
    }
  };

  /**
   * Move a waitlist item to the top
   */
  const moveWaitlistItemToTop = async (waitlistId: number) => {
    if (!eventUuid) return;

    setIsChangingPosition(true);
    try {
      await dispatch(
        updateWaitlistPositionAction(
          {
            uuid: eventUuid,
            eventWaitlistId: waitlistId,
            newPosition: 1,
          },
          () => dispatch(fetchEventWaitlistItemsAction({ uuid: eventUuid })),
        ),
      );

      if (errorUpdatePosition) {
        throw new Error(errorUpdatePosition);
      }
    } catch (error) {
      console.error("Error moving to top:", error);
      throw error;
    } finally {
      setIsChangingPosition(false);
    }
  };

  /**
   * Utility to format dates
   */
  const formatDate = (dateString: string): string => {
    try {
      return format(parseISO(dateString), "MMM d, yyyy h:mm a");
    } catch {
      return "Invalid date";
    }
  };

  /**
   * Flatten store data so UI can easily consume
   */
  const registeredUsers: RegisteredUser[] = enrollments;
  const waitlist: WaitlistEntry[] = waitlistItems;

  return {
    activeTab,
    isProcessing,
    isRefreshing,
    isMovingToWaitlist,
    isReplacing,
    isRegistering,
    isChangingPosition,
    waitlist,
    registeredUsers,
    waitlistDetails,
    showRemoveDialog,
    showMoveToWaitlistDialog,
    showReplaceDialog,
    showRegisterDialog,
    selectedUser,
    selectedRegistration,
    handleTabChange,
    refreshData,
    processWaitlist,
    removeFromWaitlist,
    moveToWaitlist,
    registerFromWaitlist,
    moveFromEnrollmentToWaitlist,
    replaceEmployee,
    updateWaitlistPosition,
    moveWaitlistItemToTop,
    formatDate,
    setShowRemoveDialog,
    setShowMoveToWaitlistDialog,
    setShowReplaceDialog,
    setShowRegisterDialog,
    setSelectedRegistration,
    setSelectedUser,
    handleRegisterClick: (user: WaitlistEntry) => {
      setSelectedUser(user);
      setShowRegisterDialog(true);
    },
    handleRemoveClick: (user: WaitlistEntry) => {
      setSelectedUser(user);
      setShowRemoveDialog(true);
    },
    handleReplaceClick: (registration: RegisteredUser) => {
      setSelectedRegistration(registration);
      setShowReplaceDialog(true);
    },
    handleMoveToWaitlistClick: (registration: RegisteredUser) => {
      setSelectedRegistration(registration);
      setShowMoveToWaitlistDialog(true);
    },
  };
}
