"use client";

import React, { useState, useEffect } from "react";
import { Search, UserPlus, Plus, Loader2, AlertCircle } from "lucide-react";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import { fetchUsers } from "@/api/fetchUsers/fetchUsersApi";
import { User } from "@/api/fetchUsers/types";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";

interface NewAttendeeFields {
  firstName: string;
  lastName: string;
  email?: string;
}

interface AddAttendeesProps {
  onAddAttendee: (attendee: NewAttendeeFields) => void;
  duplicateEmailError: string | null;
  onClearDuplicateEmailError: () => void;
}

export default function AddAttendees({
  onAddAttendee,
  duplicateEmailError,
  onClearDuplicateEmailError,
}: AddAttendeesProps) {
  const [employeeSearchQuery, setEmployeeSearchQuery] = useState("");
  const debouncedSearchQuery = useDebouncedValue(employeeSearchQuery, 500);
  const [isSearching, setIsSearching] = useState(false);
  const [searchResults, setSearchResults] = useState<User[]>([]);
  const [searchError, setSearchError] = useState<string | null>(null);
  const [newAttendeeFirstName, setNewAttendeeFirstName] = useState("");
  const [newAttendeeLastName, setNewAttendeeLastName] = useState("");
  const [newAttendeeEmail, setNewAttendeeEmail] = useState("");

  useEffect(() => {
    setSearchError(null);
    if (debouncedSearchQuery.length < 2) {
      setSearchResults([]);
      return;
    }
    setIsSearching(true);
    async function fetchEmployees() {
      try {
        const response = await fetchUsers({
          searchTerm: debouncedSearchQuery,
          pageSize: 10,
        });
        setSearchResults(response.data.users || []);
      } catch (error) {
        console.error("Error searching for employees:", error);
        setSearchError("Failed to fetch employees. Please try again.");
        setSearchResults([]);
      } finally {
        setIsSearching(false);
      }
    }
    fetchEmployees();
  }, [debouncedSearchQuery]);

  function handleSearchInputChange(e: React.ChangeEvent<HTMLInputElement>) {
    setEmployeeSearchQuery(e.target.value);
    if (duplicateEmailError) {
      onClearDuplicateEmailError();
    }
  }

  function addEmployeeAttendee(emp: User) {
    onAddAttendee({
      firstName: emp.firstName,
      lastName: emp.lastName,
      email: emp.email,
    });
  }

  function addNewAttendee() {
    onAddAttendee({
      firstName: newAttendeeFirstName,
      lastName: newAttendeeLastName,
      email: newAttendeeEmail,
    });
    if (!duplicateEmailError) {
      setNewAttendeeFirstName("");
      setNewAttendeeLastName("");
      setNewAttendeeEmail("");
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Add Attendees</CardTitle>
        <CardDescription>
          Add existing employees or create new attendees
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Tabs
          className="w-full"
          defaultValue="search"
          onValueChange={() => {
            if (duplicateEmailError) {
              onClearDuplicateEmailError();
            }
          }}
        >
          <TabsList className="mb-4 w-full flex flex-col sm:flex-row h-auto">
            <TabsTrigger
              className="w-full mb-1 sm:mb-0 justify-start sm:justify-center px-4 py-3"
              value="search"
            >
              <Search className="h-4 w-4 mr-2" />
              Search Employees
            </TabsTrigger>
            <TabsTrigger
              className="w-full justify-start sm:justify-center px-4 py-3"
              value="create"
            >
              <UserPlus className="h-4 w-4 mr-2" />
              Add New Attendee
            </TabsTrigger>
          </TabsList>
          <TabsContent className="space-y-4" value="search">
            <div>
              <Label htmlFor="employee-search">Search for employees</Label>
              <Input
                className="mt-1"
                id="employee-search"
                onChange={handleSearchInputChange}
                placeholder="Search by name or email"
                value={employeeSearchQuery}
              />
            </div>
            {duplicateEmailError && (
              <Alert variant="destructive">
                <AlertCircle className="h-4 w-4" />
                <AlertTitle>Duplicate Email</AlertTitle>
                <AlertDescription>{duplicateEmailError}</AlertDescription>
              </Alert>
            )}
            {searchError && (
              <Alert variant="destructive">
                <AlertCircle className="h-4 w-4" />
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>{searchError}</AlertDescription>
              </Alert>
            )}
            {isSearching ? (
              <div className="flex justify-center py-4">
                <Loader2 className="h-6 w-6 animate-spin text-primary" />
              </div>
            ) : (
              <div className="space-y-2">
                {searchResults.length > 0 ? (
                  searchResults.map((emp) => (
                    <div
                      className="flex flex-col sm:flex-row justify-between items-start sm:items-center border rounded-md p-2 gap-2"
                      key={emp.id}
                    >
                      <div>
                        <div className="font-medium">
                          {emp.firstName} {emp.lastName}
                        </div>
                        <div className="text-sm text-muted-foreground">
                          {emp.email}
                        </div>
                      </div>
                      <Button
                        className="self-end sm:self-auto"
                        onClick={() => addEmployeeAttendee(emp)}
                        size="sm"
                        type="button"
                      >
                        Add
                      </Button>
                    </div>
                  ))
                ) : debouncedSearchQuery.length >= 2 ? (
                  <div className="text-center py-4 text-muted-foreground">
                    No employees found matching your search...
                  </div>
                ) : (
                  <div className="text-center py-4 text-muted-foreground">
                    Type at least 2 characters to search
                  </div>
                )}
              </div>
            )}
          </TabsContent>
          <TabsContent className="space-y-4" value="create">
            {duplicateEmailError && (
              <Alert variant="destructive">
                <AlertCircle className="h-4 w-4" />
                <AlertTitle>Duplicate Email</AlertTitle>
                <AlertDescription>{duplicateEmailError}</AlertDescription>
              </Alert>
            )}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <Label htmlFor="new-attendee-first-name">First Name</Label>
                <Input
                  id="new-attendee-first-name"
                  onChange={(e) => setNewAttendeeFirstName(e.target.value)}
                  placeholder="First name"
                  value={newAttendeeFirstName}
                />
              </div>
              <div>
                <Label htmlFor="new-attendee-last-name">Last Name</Label>
                <Input
                  id="new-attendee-last-name"
                  onChange={(e) => setNewAttendeeLastName(e.target.value)}
                  placeholder="Last name"
                  value={newAttendeeLastName}
                />
              </div>
              <div>
                <Label htmlFor="new-attendee-email">Email</Label>
                <Input
                  id="new-attendee-email"
                  onChange={(e) => {
                    setNewAttendeeEmail(e.target.value);
                    if (duplicateEmailError) {
                      onClearDuplicateEmailError();
                    }
                  }}
                  placeholder="Email address"
                  type="email"
                  value={newAttendeeEmail}
                />
              </div>
            </div>
            <div className="flex justify-end">
              <Button onClick={addNewAttendee} type="button">
                <Plus className="h-4 w-4 mr-2" />
                Add Attendee
              </Button>
            </div>
          </TabsContent>
        </Tabs>
      </CardContent>
    </Card>
  );
}
