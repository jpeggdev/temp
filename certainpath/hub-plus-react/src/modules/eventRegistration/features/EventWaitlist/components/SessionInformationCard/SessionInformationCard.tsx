import React from "react";
import { Badge } from "@/components/ui/badge";
import {
  AlertTriangle,
  CheckCircle2,
  Clock,
  UserCheck,
  UserPlus,
  ShoppingBag,
} from "lucide-react";
import { WaitlistDetails } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchWaitlistDetails/types";

interface SessionInformationCardProps {
  waitlistDetails: WaitlistDetails | null;
  waitlistLength: number;
  availableSpots: number;
  formatDate: (date: string) => string;
}

export default function SessionInformationCard({
  waitlistDetails,
  waitlistLength,
  availableSpots,
  formatDate,
}: SessionInformationCardProps) {
  if (!waitlistDetails) {
    return (
      <div className="bg-white/70 dark:bg-gray-800/70 p-6 rounded-lg">
        <p className="text-gray-700 dark:text-gray-300">
          Loading session info...
        </p>
      </div>
    );
  }

  const {
    name,
    startDate,
    enrolledCount,
    checkoutReservedCount,
    maxEnrollments,
  } = waitlistDetails;
  const isFull = waitlistDetails.availableSeatCount <= 0;

  return (
    <div className="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-lg border border-white/50 dark:border-gray-700/50 p-6 shadow-sm">
      <div className="flex items-start justify-between mb-4">
        <div className="flex-1">
          <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {name || "Untitled Event"}
          </h2>
          <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <Clock className="h-4 w-4" />
            <span>{startDate ? formatDate(startDate) : "No start date"}</span>
          </div>
        </div>
        <div className="flex items-center gap-2">
          {isFull ? (
            <Badge className="px-3 py-1" variant="destructive">
              <AlertTriangle className="h-3 w-3 mr-1" />
              Session Full
            </Badge>
          ) : (
            <Badge
              className="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
              variant="default"
            >
              <CheckCircle2 className="h-3 w-3 mr-1" />
              Open for Registration
            </Badge>
          )}
        </div>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200/50 dark:border-blue-800/50">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-blue-600 dark:text-blue-400">
                Registered
              </p>
              <p className="text-2xl font-bold text-blue-700 dark:text-blue-300">
                {enrolledCount}/{maxEnrollments}
              </p>
            </div>
            <div className="p-2 bg-blue-100 dark:bg-blue-800/30 rounded-lg">
              <UserCheck className="h-5 w-5 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
          <p className="text-xs text-blue-600 dark:text-blue-400 mt-1">
            Current enrollment status
          </p>
        </div>
        <div className="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200/50 dark:border-green-800/50">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-green-600 dark:text-green-400">
                Available Spots
              </p>
              <p className="text-2xl font-bold text-green-700 dark:text-green-300">
                {availableSpots}
              </p>
            </div>
            <div className="p-2 bg-green-100 dark:bg-green-800/30 rounded-lg">
              <UserPlus className="h-5 w-5 text-green-600 dark:text-green-400" />
            </div>
          </div>
          <p className="text-xs text-green-600 dark:text-green-400 mt-1">
            {availableSpots > 0
              ? "Ready for registration"
              : "Session at capacity"}
          </p>
        </div>
        <div className="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200/50 dark:border-amber-800/50">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-amber-600 dark:text-amber-400">
                On Waitlist
              </p>
              <p className="text-2xl font-bold text-amber-700 dark:text-amber-300">
                {waitlistLength}
              </p>
            </div>
            <div className="p-2 bg-amber-100 dark:bg-amber-800/30 rounded-lg">
              <Clock className="h-5 w-5 text-amber-600 dark:text-amber-400" />
            </div>
          </div>
          <p className="text-xs text-amber-600 dark:text-amber-400 mt-1">
            Users waiting for spots
          </p>
        </div>
        <div className="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200/50 dark:border-purple-800/50">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-purple-600 dark:text-purple-400">
                Checkout Reserved
              </p>
              <p className="text-2xl font-bold text-purple-700 dark:text-purple-300">
                {checkoutReservedCount}
              </p>
            </div>
            <div className="p-2 bg-purple-100 dark:bg-purple-800/30 rounded-lg">
              <ShoppingBag className="h-5 w-5 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
          <p className="text-xs text-purple-600 dark:text-purple-400 mt-1">
            Spots temporarily held during checkout
          </p>
        </div>
      </div>
    </div>
  );
}
