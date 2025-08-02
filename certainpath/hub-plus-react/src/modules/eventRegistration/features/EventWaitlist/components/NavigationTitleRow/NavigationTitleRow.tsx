import React from "react";
import { ArrowLeft, RefreshCw, UserCheck, Loader2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useNavigate, useParams } from "react-router-dom";

interface NavigationTitleRowProps {
  isRefreshing: boolean;
  refreshData: () => void;
  isProcessing: boolean;
  processWaitlist: () => void;
  waitlistLength: number;
}

export default function NavigationTitleRow({
  isRefreshing,
  refreshData,
  isProcessing,
  processWaitlist,
  waitlistLength,
}: NavigationTitleRowProps) {
  const navigate = useNavigate();
  const { eventUuid } = useParams<{ eventUuid: string }>();

  const handleBack = () => {
    navigate(`/event-registration/admin/events/${eventUuid}/sessions`);
  };

  return (
    <div className="flex items-center justify-between mb-6">
      <div className="flex items-center gap-4">
        <Button
          className="shadow-sm hover:shadow-md transition-shadow"
          onClick={handleBack}
          size="icon"
          variant="outline"
        >
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h1 className="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            Waitlist Management
          </h1>
          <p className="text-sm text-muted-foreground mt-1">
            Manage session registrations and waitlist
          </p>
        </div>
      </div>
      <div className="flex gap-3">
        <Button
          className="shadow-sm hover:shadow-md transition-all duration-200"
          disabled={isRefreshing}
          onClick={refreshData}
          size="sm"
          variant="outline"
        >
          {isRefreshing ? (
            <>
              <Loader2 className="mr-2 h-4 w-4 animate-spin" />
              Refreshing...
            </>
          ) : (
            <>
              <RefreshCw className="mr-2 h-4 w-4" />
              Refresh Data
            </>
          )}
        </Button>
        <Button
          className="shadow-sm hover:shadow-md transition-all duration-200 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
          disabled={isProcessing || waitlistLength === 0}
          onClick={processWaitlist}
          size="sm"
          variant="default"
        >
          {isProcessing ? (
            <>
              <Loader2 className="mr-2 h-4 w-4 animate-spin" />
              Processing...
            </>
          ) : (
            <>
              <UserCheck className="mr-2 h-4 w-4" />
              Process Waitlist
            </>
          )}
        </Button>
      </div>
    </div>
  );
}
