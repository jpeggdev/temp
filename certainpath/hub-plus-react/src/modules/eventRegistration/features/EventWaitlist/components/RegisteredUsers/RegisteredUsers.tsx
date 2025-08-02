import React from "react";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { MoveRight, UserCog } from "lucide-react";
import { EventEnrollmentItemResponseDTO } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventEnrollments/types";
import { WaitlistDetails } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchWaitlistDetails/types";
import { RegisteredUser } from "@/modules/eventRegistration/features/EventWaitlist/hooks/useWaitlist";

interface RegisteredUsersProps {
  waitlistDetails: WaitlistDetails | null;
  registeredUsers: EventEnrollmentItemResponseDTO[];
  formatDate: (dateString: string) => string;
  handleReplaceClick: (registration: RegisteredUser) => void;
  handleMoveToWaitlistClick: (registration: RegisteredUser) => void;
}

export default function RegisteredUsers({
  registeredUsers,
  formatDate,
  handleReplaceClick,
  handleMoveToWaitlistClick,
}: RegisteredUsersProps) {
  const waitlistIsEnabled = true;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Registered Users</CardTitle>
        <CardDescription>
          Users currently registered for this session.
        </CardDescription>
      </CardHeader>
      <CardContent>
        {registeredUsers.length === 0 ? (
          <div className="text-center py-8 text-muted-foreground">
            No users registered for this session.
          </div>
        ) : (
          <div className="border rounded-md">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>User</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Registration Date</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {registeredUsers.map((user) => (
                  <TableRow key={user.id}>
                    <TableCell className="font-medium">
                      {user.firstName} {user.lastName}
                    </TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell>{formatDate(user.enrolledAt || "")}</TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button
                          onClick={() => handleReplaceClick(user)}
                          size="sm"
                          variant="outline"
                        >
                          <UserCog className="h-4 w-4 mr-1" />
                          Replace
                        </Button>
                        <Button
                          disabled={!waitlistIsEnabled}
                          onClick={() => handleMoveToWaitlistClick(user)}
                          size="sm"
                          title={
                            !waitlistIsEnabled
                              ? "Waitlist is disabled for this session"
                              : ""
                          }
                          variant="outline"
                        >
                          <MoveRight className="h-4 w-4 mr-1" />
                          Move to Waitlist
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
