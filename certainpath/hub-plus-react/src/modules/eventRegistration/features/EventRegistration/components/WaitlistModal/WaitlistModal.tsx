import React, { useEffect, useState } from "react";
import Modal from "react-modal";
import {
  X,
  ArrowRightLeft,
  Save,
  AlertCircle,
  UserMinus,
  UserPlus,
  Loader2,
} from "lucide-react";
import { useDispatch, useSelector } from "react-redux";
import type { EventCheckoutSessionAttendee } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/types";
import { RootState } from "@/app/rootReducer";
import { AttendeeWaitlistRequest } from "@/modules/eventRegistration/features/EventRegistration/api/updateEventCheckoutAttendeeWaitlist/types";
import {
  getEventCheckoutSessionDetailsAction,
  updateEventCheckoutAttendeeWaitlistAction,
} from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutSlice";

interface WaitlistModalProps {
  isOpen: boolean;
  onClose: () => void;
  allAttendees: EventCheckoutSessionAttendee[];
  eventName: string;
  occupiedAttendeeSeatsByCurrentUser: number;
  minimumOffWaitlist?: number;
  checkoutSessionUuid: string;
}

function WaitlistModal({
  isOpen,
  onClose,
  allAttendees,
  eventName,
  occupiedAttendeeSeatsByCurrentUser,
  minimumOffWaitlist = 1,
  checkoutSessionUuid,
}: WaitlistModalProps) {
  const dispatch = useDispatch();
  const loadingUpdateWaitlist = useSelector(
    (state: RootState) => state.eventCheckout.loadingUpdateWaitlist,
  );
  const updateWaitlistError = useSelector(
    (state: RootState) => state.eventCheckout.updateWaitlistError,
  );

  const [localAttendees, setLocalAttendees] = useState<
    EventCheckoutSessionAttendee[]
  >([]);
  const [hasChanges, setHasChanges] = useState(false);
  const [selectedConfirmed, setSelectedConfirmed] = useState<number | null>(
    null,
  );
  const [selectedWaitlisted, setSelectedWaitlisted] = useState<number | null>(
    null,
  );

  useEffect(() => {
    const selectedAttendees = allAttendees.filter((a) => a.isSelected);
    setLocalAttendees(selectedAttendees);
    setHasChanges(false);
    setSelectedConfirmed(null);
    setSelectedWaitlisted(null);
  }, [allAttendees, isOpen]);

  const confirmedAttendees = localAttendees.filter((a) => !a.isWaitlist);
  const waitlistedAttendees = localAttendees.filter((a) => a.isWaitlist);
  const confirmedCount = confirmedAttendees.length;
  const atMinimumConfirmed = confirmedCount <= minimumOffWaitlist;
  const atMaximumConfirmed =
    confirmedCount >= occupiedAttendeeSeatsByCurrentUser;

  function moveToWaitlist(attendeeId: number) {
    if (atMinimumConfirmed && selectedWaitlisted === null) {
      setSelectedConfirmed(attendeeId);
      return;
    }
    setLocalAttendees((prev) => {
      let updatedAttendees;
      if (selectedWaitlisted !== null) {
        updatedAttendees = prev.map((attendee) => {
          if (attendee.id === attendeeId) {
            return { ...attendee, isWaitlist: true };
          } else if (attendee.id === selectedWaitlisted) {
            return { ...attendee, isWaitlist: false };
          }
          return attendee;
        });
        setSelectedConfirmed(null);
        setSelectedWaitlisted(null);
      } else {
        updatedAttendees = prev.map((attendee) =>
          attendee.id === attendeeId
            ? { ...attendee, isWaitlist: true }
            : attendee,
        );
        setSelectedConfirmed(null);
      }
      return updatedAttendees;
    });
    setHasChanges(true);
  }

  function confirmAttendee(attendeeId: number) {
    if (atMaximumConfirmed && selectedConfirmed === null) {
      setSelectedWaitlisted(attendeeId);
      return;
    }
    setLocalAttendees((prev) => {
      let updatedAttendees;
      if (selectedConfirmed !== null) {
        updatedAttendees = prev.map((attendee) => {
          if (attendee.id === attendeeId) {
            return { ...attendee, isWaitlist: false };
          } else if (attendee.id === selectedConfirmed) {
            return { ...attendee, isWaitlist: true };
          }
          return attendee;
        });
        setSelectedConfirmed(null);
        setSelectedWaitlisted(null);
      } else {
        updatedAttendees = prev.map((attendee) =>
          attendee.id === attendeeId
            ? { ...attendee, isWaitlist: false }
            : attendee,
        );
        setSelectedWaitlisted(null);
      }
      return updatedAttendees;
    });
    setHasChanges(true);
  }

  function cancelSelection() {
    setSelectedConfirmed(null);
    setSelectedWaitlisted(null);
  }

  function handleSave() {
    const attendeeUpdates: AttendeeWaitlistRequest[] = localAttendees.map(
      (attendee) => ({
        attendeeId: attendee.id,
        isWaitlist: attendee.isWaitlist,
      }),
    );
    dispatch(
      updateEventCheckoutAttendeeWaitlistAction(
        checkoutSessionUuid,
        { attendees: attendeeUpdates },
        () => {
          dispatch(getEventCheckoutSessionDetailsAction(checkoutSessionUuid));
          onClose();
        },
      ),
    );
  }

  return (
    <Modal
      contentLabel="Waitlist Management Modal"
      isOpen={isOpen}
      onRequestClose={onClose}
      style={{
        content: {
          top: "50%",
          left: "50%",
          transform: "translate(-50%, -50%)",
          borderRadius: "8px",
          padding: "24px",
          background: "white",
          boxShadow: "0 10px 25px rgba(0,0,0,.3)",
          width: "calc(100% - 32px)",
          maxWidth: "900px",
          height: "80vh",
          maxHeight: "800px",
          overflowY: "auto",
        },
        overlay: {
          backgroundColor: "rgba(0,0,0,0.5)",
          zIndex: 9999,
        },
      }}
    >
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-xl font-semibold text-gray-900 dark:text-gray-100">
          Manage Attendees for {eventName}
        </h3>
        <button
          className="text-gray-400 hover:text-gray-600 dark:hover:text-white"
          onClick={onClose}
          type="button"
        >
          <X className="w-5 h-5" />
        </button>
      </div>
      <div className="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 text-blue-700">
        <div className="font-medium mb-1">Seating Information:</div>
        <ul className="list-disc pl-5 text-sm">
          <li>
            Your registration allows for{" "}
            <strong>{occupiedAttendeeSeatsByCurrentUser}</strong> confirmed{" "}
            {occupiedAttendeeSeatsByCurrentUser === 1 ? "seat" : "seats"}
          </li>
          <li>
            You must have at least <strong>{minimumOffWaitlist}</strong>{" "}
            confirmed {minimumOffWaitlist === 1 ? "attendee" : "attendees"}
          </li>
          <li>
            Current status: <strong>{confirmedCount}</strong> confirmed{" "}
            {confirmedCount === 1 ? "attendee" : "attendees"},{" "}
            <strong>{waitlistedAttendees.length}</strong> on waitlist
          </li>
        </ul>
      </div>
      {updateWaitlistError && (
        <div className="mb-4 bg-red-50 border-l-4 border-red-500 p-4 text-red-700">
          <p className="font-medium">Error:</p>
          <p>{updateWaitlistError}</p>
        </div>
      )}
      {(selectedConfirmed !== null || selectedWaitlisted !== null) && (
        <div className="mb-4 bg-indigo-50 border border-indigo-200 p-3 rounded-md flex items-center gap-2 text-indigo-700">
          <ArrowRightLeft className="w-5 h-5 flex-shrink-0" />
          <div>
            <p className="font-medium">Swap mode active</p>
            <p className="text-sm">
              {selectedConfirmed !== null
                ? "Select someone from the waitlist to swap with."
                : "Select someone from the confirmed list to swap with."}
            </p>
          </div>
          <button
            className="ml-auto bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-2 py-1 rounded text-sm"
            onClick={cancelSelection}
          >
            Cancel
          </button>
        </div>
      )}
      {(atMinimumConfirmed || atMaximumConfirmed) &&
        !selectedConfirmed &&
        !selectedWaitlisted && (
          <div className="mb-4 bg-amber-50 border border-amber-200 p-3 rounded-md flex items-center gap-2 text-amber-700">
            <AlertCircle className="w-5 h-5" />
            <span>
              You've reached the maximum number of confirmed attendees. To make
              changes, you'll need to swap attendees between lists.
            </span>
          </div>
        )}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          className={`border rounded-lg overflow-hidden ${
            selectedWaitlisted !== null
              ? "border-indigo-300 shadow-md"
              : atMaximumConfirmed
                ? "border-green-300 shadow-sm"
                : ""
          }`}
        >
          <div
            className={`p-3 border-b ${
              selectedWaitlisted !== null
                ? "bg-indigo-50"
                : atMaximumConfirmed
                  ? "bg-green-50"
                  : "bg-gray-50"
            }`}
          >
            <div className="flex justify-between items-center">
              <h4 className="font-semibold text-gray-800">
                Confirmed Attendees
              </h4>
              <span
                className={`px-2 py-1 rounded-full text-sm font-medium ${
                  selectedWaitlisted !== null
                    ? "bg-indigo-100 text-indigo-800"
                    : atMaximumConfirmed
                      ? "bg-green-100 text-green-800"
                      : "bg-gray-100 text-gray-600"
                }`}
              >
                {confirmedCount}/{occupiedAttendeeSeatsByCurrentUser}
              </span>
            </div>
            <p className="text-sm text-gray-600">
              These attendees have confirmed seats for the event
            </p>
          </div>
          <div className="overflow-y-auto" style={{ maxHeight: "400px" }}>
            {confirmedAttendees.length === 0 ? (
              <div className="p-4 text-center text-gray-500 italic">
                No confirmed attendees
              </div>
            ) : (
              <ul className="divide-y">
                {confirmedAttendees.map((attendee) => (
                  <li
                    className={`p-3 hover:bg-gray-50 ${
                      selectedConfirmed === attendee.id ? "bg-indigo-50" : ""
                    } cursor-pointer`}
                    key={attendee.id}
                    onClick={() => {
                      if (selectedWaitlisted !== null) {
                        moveToWaitlist(attendee.id);
                      } else if (selectedConfirmed === attendee.id) {
                        setSelectedConfirmed(null);
                      } else if (atMinimumConfirmed) {
                        setSelectedConfirmed(attendee.id);
                      } else {
                        moveToWaitlist(attendee.id);
                      }
                    }}
                  >
                    <div className="flex justify-between items-center">
                      <div>
                        <div className="font-medium">
                          {attendee.firstName} {attendee.lastName}
                        </div>
                        <div className="text-sm text-gray-600">
                          {attendee.email}
                        </div>
                        {attendee.specialRequests && (
                          <div className="text-xs text-gray-500 mt-1">
                            <span className="font-medium">Requests:</span>{" "}
                            {attendee.specialRequests}
                          </div>
                        )}
                      </div>
                      <div>
                        {selectedWaitlisted !== null ? (
                          <button
                            className="p-2 rounded-full bg-indigo-100 text-indigo-700 hover:bg-indigo-200"
                            title="Swap with selected waitlisted attendee"
                          >
                            <ArrowRightLeft className="w-4 h-4" />
                          </button>
                        ) : selectedConfirmed === attendee.id ? (
                          <button
                            className="p-2 rounded-full bg-indigo-100 text-indigo-700 hover:bg-indigo-200"
                            title="Selected for swap - click again to cancel"
                          >
                            <ArrowRightLeft className="w-4 h-4" />
                          </button>
                        ) : atMinimumConfirmed ? (
                          <button
                            className="p-2 rounded-full bg-amber-50 text-amber-700 hover:bg-amber-100"
                            title="Select for swap (required at minimum attendees)"
                          >
                            <UserMinus className="w-4 h-4" />
                          </button>
                        ) : (
                          <button
                            className="p-2 rounded-full bg-amber-50 text-amber-700 hover:bg-amber-100"
                            title="Move to waitlist"
                          >
                            <UserMinus className="w-4 h-4" />
                          </button>
                        )}
                      </div>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
        <div
          className={`border rounded-lg overflow-hidden ${
            selectedConfirmed !== null ? "border-indigo-300 shadow-md" : ""
          }`}
        >
          <div
            className={`p-3 border-b ${
              selectedConfirmed !== null ? "bg-indigo-50" : "bg-gray-50"
            }`}
          >
            <div className="flex justify-between items-center">
              <h4 className="font-semibold text-gray-800">
                Waitlisted Attendees
              </h4>
              <span className="px-2 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                {waitlistedAttendees.length}
              </span>
            </div>
            <p className="text-sm text-gray-600">
              These attendees are on the waitlist
            </p>
          </div>
          <div className="overflow-y-auto" style={{ maxHeight: "400px" }}>
            {waitlistedAttendees.length === 0 ? (
              <div className="p-4 text-center text-gray-500 italic">
                No attendees on the waitlist
              </div>
            ) : (
              <ul className="divide-y">
                {waitlistedAttendees.map((attendee) => (
                  <li
                    className={`p-3 hover:bg-gray-50 ${
                      selectedWaitlisted === attendee.id ? "bg-indigo-50" : ""
                    } cursor-pointer`}
                    key={attendee.id}
                    onClick={() => {
                      if (selectedConfirmed !== null) {
                        confirmAttendee(attendee.id);
                      } else if (selectedWaitlisted === attendee.id) {
                        setSelectedWaitlisted(null);
                      } else if (atMaximumConfirmed) {
                        setSelectedWaitlisted(attendee.id);
                      } else {
                        confirmAttendee(attendee.id);
                      }
                    }}
                  >
                    <div className="flex justify-between items-center">
                      <div>
                        <div className="font-medium">
                          {attendee.firstName} {attendee.lastName}
                        </div>
                        <div className="text-sm text-gray-600">
                          {attendee.email}
                        </div>
                        {attendee.specialRequests && (
                          <div className="text-xs text-gray-500 mt-1">
                            <span className="font-medium">Requests:</span>{" "}
                            {attendee.specialRequests}
                          </div>
                        )}
                      </div>
                      <div>
                        {selectedConfirmed !== null ? (
                          <button
                            className="p-2 rounded-full bg-indigo-100 text-indigo-700 hover:bg-indigo-200"
                            title="Swap with selected confirmed attendee"
                          >
                            <ArrowRightLeft className="w-4 h-4" />
                          </button>
                        ) : selectedWaitlisted === attendee.id ? (
                          <button
                            className="p-2 rounded-full bg-indigo-100 text-indigo-700 hover:bg-indigo-200"
                            title="Selected for swap - click again to cancel"
                          >
                            <ArrowRightLeft className="w-4 h-4" />
                          </button>
                        ) : atMaximumConfirmed ? (
                          <button
                            className="p-2 rounded-full bg-green-50 text-green-700 hover:bg-green-100"
                            title="Select for swap (required at maximum attendees)"
                          >
                            <UserPlus className="w-4 h-4" />
                          </button>
                        ) : (
                          <button
                            className="p-2 rounded-full bg-green-50 text-green-700 hover:bg-green-100"
                            title="Confirm attendee"
                          >
                            <UserPlus className="w-4 h-4" />
                          </button>
                        )}
                      </div>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>
      <div className="mt-6 flex justify-end gap-3">
        <button
          className="px-4 py-2 rounded border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-neutral-700 hover:bg-gray-100 text-sm"
          onClick={onClose}
          type="button"
        >
          Cancel
        </button>
        <button
          className={`px-4 py-2 rounded text-sm flex items-center gap-1.5 ${
            hasChanges
              ? "bg-blue-600 hover:bg-blue-700 text-white"
              : "bg-blue-100 text-blue-400 cursor-not-allowed"
          }`}
          disabled={!hasChanges || loadingUpdateWaitlist}
          onClick={handleSave}
          type="button"
        >
          {loadingUpdateWaitlist ? (
            <>
              <Loader2 className="w-4 h-4 animate-spin" />
              Saving...
            </>
          ) : (
            <>
              <Save className="w-4 h-4" />
              Save Changes
            </>
          )}
        </button>
      </div>
    </Modal>
  );
}

export default WaitlistModal;
