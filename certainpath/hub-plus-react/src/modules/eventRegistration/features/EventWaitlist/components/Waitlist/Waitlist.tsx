import React from "react";
import { useParams } from "react-router-dom";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import useWaitlist from "@/modules/eventRegistration/features/EventWaitlist/hooks/useWaitlist";
import RegisteredUsers from "@/modules/eventRegistration/features/EventWaitlist/components/RegisteredUsers/RegisteredUsers";
import NavigationTitleRow from "@/modules/eventRegistration/features/EventWaitlist/components/NavigationTitleRow/NavigationTitleRow";
import SessionInformationCard from "@/modules/eventRegistration/features/EventWaitlist/components/SessionInformationCard/SessionInformationCard";
import WaitlistTable from "@/modules/eventRegistration/features/EventWaitlist/components/WaitlistTable/WaitlistTable";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ConfirmRemoveWaitlistModal from "@/modules/eventRegistration/features/EventWaitlist/components/ConfirmRemoveWaitlistModal/ConfirmRemoveWaitlistModal";
import ConfirmRegisterWaitlistModal from "@/modules/eventRegistration/features/EventWaitlist/components/ConfirmRegisterWaitlistModal/ConfirmRegisterWaitlistModal";
import ConfirmMoveEnrollmentToWaitlistModal from "@/modules/eventRegistration/features/EventWaitlist/components/ConfirmMoveEnrollmentToWaitlistModal/ConfirmMoveEnrollmentToWaitlistModal";
import ConfirmReplaceEnrollmentModal from "@/modules/eventRegistration/features/EventWaitlist/components/ConfirmReplaceEnrollmentModal/ConfirmReplaceEnrollmentModal";

export default function Waitlist() {
  const { uuid } = useParams<{ uuid: string }>();

  const {
    activeTab,
    isRefreshing,
    isProcessing,
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
    registerFromWaitlist,
    moveFromEnrollmentToWaitlist,
    updateWaitlistPosition,
    moveWaitlistItemToTop,
    formatDate,
    setShowRemoveDialog,
    setShowMoveToWaitlistDialog,
    setShowReplaceDialog,
    setShowRegisterDialog,
    handleRegisterClick,
    handleRemoveClick,
    handleReplaceClick,
    handleMoveToWaitlistClick,
  } = useWaitlist({
    eventUuid: uuid,
  });

  const isSessionFull =
    !!waitlistDetails && waitlistDetails.availableSeatCount <= 0;
  const availableSpots = waitlistDetails
    ? waitlistDetails.availableSeatCount
    : 0;

  // For the waitlist user
  const selectedUserFullName = selectedUser
    ? [selectedUser.firstName, selectedUser.lastName].filter(Boolean).join(" ")
    : "Unknown";

  // For the enrollment user we are demoting
  const selectedRegistrationFullName = selectedRegistration
    ? [selectedRegistration.firstName, selectedRegistration.lastName]
        .filter(Boolean)
        .join(" ")
    : "Unknown";

  return (
    <MainPageWrapper title="Waitlist Management">
      <div className="relative mb-8 overflow-hidden rounded-xl border border-blue-200/50 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:border-gray-700 dark:from-gray-900 dark:via-blue-900/20 dark:to-purple-900/20">
        <div className="absolute inset-0 bg-grid-pattern opacity-5"></div>
        <div className="relative p-6">
          <NavigationTitleRow
            isProcessing={isProcessing}
            isRefreshing={isRefreshing}
            processWaitlist={processWaitlist}
            refreshData={refreshData}
            waitlistLength={waitlist.length}
          />
          <SessionInformationCard
            availableSpots={availableSpots}
            formatDate={formatDate}
            waitlistDetails={waitlistDetails}
            waitlistLength={waitlist.length}
          />
        </div>
      </div>

      <Tabs
        className="w-full"
        onValueChange={handleTabChange}
        value={activeTab}
      >
        <TabsList className="mb-4">
          <TabsTrigger className="flex items-center gap-2" value="waitlist">
            Waitlist
            <Badge variant="outline">{waitlist.length}</Badge>
          </TabsTrigger>
          <TabsTrigger className="flex items-center gap-2" value="registered">
            Registered
            <Badge variant="outline">{registeredUsers.length}</Badge>
          </TabsTrigger>
        </TabsList>

        <TabsContent value="waitlist">
          <Card>
            <CardHeader>
              <CardTitle>Waitlist</CardTitle>
              <CardDescription>
                Users on the waiting list for this session.
              </CardDescription>
            </CardHeader>
            <CardContent>
              {waitlist.length === 0 ? (
                <div className="py-8 text-center text-muted-foreground">
                  No users on the waitlist for this session.
                </div>
              ) : (
                <WaitlistTable
                  formatDate={formatDate}
                  handleRegisterClick={handleRegisterClick}
                  handleRemoveClick={handleRemoveClick}
                  isSessionFull={isSessionFull}
                  onMoveToTop={moveWaitlistItemToTop}
                  onPositionChange={updateWaitlistPosition}
                  waitlist={waitlist}
                />
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="registered">
          <RegisteredUsers
            formatDate={formatDate}
            handleMoveToWaitlistClick={handleMoveToWaitlistClick}
            handleReplaceClick={handleReplaceClick}
            registeredUsers={registeredUsers}
            waitlistDetails={waitlistDetails}
          />
        </TabsContent>
      </Tabs>

      {/* REMOVE FROM WAITLIST */}
      <ConfirmRemoveWaitlistModal
        isOpen={showRemoveDialog}
        onClose={() => setShowRemoveDialog(false)}
        onConfirm={async () => {
          if (selectedUser) {
            await removeFromWaitlist(selectedUser.id);
          }
        }}
        userFullName={selectedUserFullName}
      />

      {/* REGISTER FROM WAITLIST */}
      <ConfirmRegisterWaitlistModal
        isOpen={showRegisterDialog}
        onClose={() => setShowRegisterDialog(false)}
        onConfirm={async () => {
          if (selectedUser) {
            await registerFromWaitlist(selectedUser.id);
          }
        }}
        userFullName={selectedUserFullName}
      />

      {/* MOVE ENROLLMENT -> WAITLIST */}
      <ConfirmMoveEnrollmentToWaitlistModal
        isOpen={showMoveToWaitlistDialog}
        onClose={() => setShowMoveToWaitlistDialog(false)}
        onConfirm={async () => {
          if (selectedRegistration) {
            await moveFromEnrollmentToWaitlist(selectedRegistration.id);
          }
        }}
        userFullName={selectedRegistrationFullName}
      />

      {/* REPLACE ENROLLMENT */}
      <ConfirmReplaceEnrollmentModal
        eventUuid={uuid}
        isOpen={showReplaceDialog}
        onClose={() => setShowReplaceDialog(false)}
        onSuccess={refreshData}
        selectedRegistration={selectedRegistration}
      />
    </MainPageWrapper>
  );
}
