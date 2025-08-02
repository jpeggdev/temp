import React, { useState, useEffect } from "react";
import { Search, Plus, Loader2 } from "lucide-react";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogFooter,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { useToast } from "@/components/ui/use-toast";
import { useDispatch } from "react-redux";
import { AppDispatch } from "@/app/store";
import {
  replaceEnrollmentAttendeeAction,
  replaceEnrollmentWithEmployeeAction,
} from "@/modules/eventRegistration/features/EventWaitlist/slices/enrollmentsWaitlistSlice";
import {
  EventEnrollmentItemResponseDTO,
  ReplacementType,
} from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventEnrollments/types";

interface ConfirmReplaceEnrollmentModalProps {
  isOpen: boolean;
  onClose: () => void;
  selectedRegistration: EventEnrollmentItemResponseDTO | null;
  eventUuid: string | undefined;
  onSuccess: () => Promise<void>;
}

export default function ConfirmReplaceEnrollmentModal({
  isOpen,
  onClose,
  selectedRegistration,
  eventUuid,
  onSuccess,
}: ConfirmReplaceEnrollmentModalProps) {
  const { toast } = useToast();
  const dispatch = useDispatch<AppDispatch>();

  const [activeTab, setActiveTab] = useState<string>("search");
  const [searchTerm, setSearchTerm] = useState<string>("");
  const [isSearching, setIsSearching] = useState<boolean>(false);
  const [isCreating, setIsCreating] = useState<boolean>(false);
  const [filteredResults, setFilteredResults] = useState<ReplacementType[]>([]);

  // Form state for adding new employee
  const [newEmployee, setNewEmployee] = useState({
    firstName: "",
    lastName: "",
    email: "",
    cellPhone: "",
  });

  // Reset state when dialog opens or selectedRegistration changes
  useEffect(() => {
    if (isOpen && selectedRegistration) {
      setSearchTerm("");
      setFilteredResults(selectedRegistration.replacements || []);
      setActiveTab("search");
      setNewEmployee({
        firstName: "",
        lastName: "",
        email: "",
        cellPhone: "",
      });
    }
  }, [isOpen, selectedRegistration]);

  // Filter the replacements based on search term
  useEffect(() => {
    if (!selectedRegistration?.replacements) {
      setFilteredResults([]);
      return;
    }

    if (!searchTerm.trim()) {
      setFilteredResults(selectedRegistration.replacements);
      return;
    }

    const term = searchTerm.toLowerCase();
    const results = selectedRegistration.replacements.filter((emp) => {
      // Safely check if properties exist before calling toLowerCase()
      const firstNameMatch = emp.firstName
        ? emp.firstName.toLowerCase().includes(term)
        : false;
      const lastNameMatch = emp.lastName
        ? emp.lastName.toLowerCase().includes(term)
        : false;
      const emailMatch = emp.workEmail
        ? emp.workEmail.toLowerCase().includes(term)
        : false;

      return firstNameMatch || lastNameMatch || emailMatch;
    });

    setFilteredResults(results);
  }, [searchTerm, selectedRegistration]);

  // Handle search input changes
  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearchTerm(e.target.value);
  };

  // Handle search form submission
  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSearching(true);

    // Simulate search delay
    setTimeout(() => {
      setIsSearching(false);
    }, 300);
  };

  // Handle form input changes for new employee
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setNewEmployee((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  // Handle employee selection
  const handleEmployeeSelect = async (employee: ReplacementType) => {
    if (!eventUuid || !selectedRegistration) return;

    try {
      await dispatch(
        replaceEnrollmentWithEmployeeAction({
          uuid: eventUuid,
          eventEnrollmentId: selectedRegistration.id,
          employeeId: employee.employeeId,
        }),
      );

      toast({
        title: "Replacement successful",
        description:
          `Successfully replaced with ${employee.firstName || ""} ${employee.lastName || ""}`.trim(),
      });

      onClose();
      await onSuccess();
    } catch (error) {
      console.error("Error replacing employee:", error);
      toast({
        title: "Error",
        description:
          error instanceof Error ? error.message : "Failed to replace employee",
        variant: "destructive",
      });
    }
  };

  // Handle creating a new employee
  const createEmployee = async () => {
    try {
      // Validate form
      if (!newEmployee.firstName.trim()) {
        toast({
          title: "Error",
          description: "First name is required",
          variant: "destructive",
        });
        return;
      }

      if (!newEmployee.lastName.trim()) {
        toast({
          title: "Error",
          description: "Last name is required",
          variant: "destructive",
        });
        return;
      }

      if (!newEmployee.email.trim()) {
        toast({
          title: "Error",
          description: "Email is required",
          variant: "destructive",
        });
        return;
      }

      if (!eventUuid || !selectedRegistration) return;

      setIsCreating(true);

      await dispatch(
        replaceEnrollmentAttendeeAction({
          uuid: eventUuid,
          eventEnrollmentId: selectedRegistration.id,
          newFirstName: newEmployee.firstName,
          newLastName: newEmployee.lastName,
          newEmail: newEmployee.email,
        }),
      );

      toast({
        title: "Success",
        description: "New employee added and enrollment replaced successfully",
      });

      onClose();
      await onSuccess();
    } catch (error) {
      console.error("Error creating employee:", error);
      toast({
        title: "Error",
        description:
          error instanceof Error ? error.message : "Failed to create employee",
        variant: "destructive",
      });
    } finally {
      setIsCreating(false);
    }
  };

  const companyName = selectedRegistration?.companyName || "the company";

  return (
    <Dialog onOpenChange={onClose} open={isOpen}>
      <DialogContent className="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Replace Employee</DialogTitle>
          <DialogDescription>
            {selectedRegistration
              ? `Replace ${selectedRegistration.firstName || ""} ${selectedRegistration.lastName || ""} with another employee from ${companyName}.`.trim()
              : "Replace the enrolled user with another employee."}
          </DialogDescription>
        </DialogHeader>

        <Tabs
          defaultValue="search"
          onValueChange={setActiveTab}
          value={activeTab}
        >
          <TabsList className="grid w-full grid-cols-2">
            <TabsTrigger value="search">Search Employees</TabsTrigger>
            <TabsTrigger value="add">Add New Employee</TabsTrigger>
          </TabsList>

          <TabsContent className="space-y-4" value="search">
            <form
              className="flex items-center space-x-2"
              onSubmit={handleSearchSubmit}
            >
              <div className="grid flex-1 gap-2">
                <Label className="sr-only" htmlFor="search">
                  Search
                </Label>
                <Input
                  id="search"
                  onChange={handleSearchChange}
                  placeholder="Search by name or email..."
                  value={searchTerm}
                />
              </div>
              <Button disabled={isSearching} type="submit">
                {isSearching ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <Search className="h-4 w-4" />
                )}
                <span className="sr-only">Search</span>
              </Button>
            </form>

            <div className="border rounded-md max-h-[300px] overflow-y-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead className="text-right">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredResults.length === 0 ? (
                    <TableRow>
                      <TableCell
                        className="text-center py-4 text-muted-foreground"
                        colSpan={3}
                      >
                        {isSearching
                          ? "Searching..."
                          : "No employees found. Try a different search or add a new employee."}
                      </TableCell>
                    </TableRow>
                  ) : (
                    filteredResults.map((employee) => (
                      <TableRow key={employee.employeeId}>
                        <TableCell className="font-medium">
                          {employee.firstName || ""} {employee.lastName || ""}
                        </TableCell>
                        <TableCell>{employee.workEmail || "-"}</TableCell>
                        <TableCell className="text-right">
                          <Button
                            onClick={() => handleEmployeeSelect(employee)}
                            size="sm"
                            variant="outline"
                          >
                            Select
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </div>
          </TabsContent>

          <TabsContent className="space-y-4" value="add">
            <div className="grid gap-4 py-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="firstName">First Name *</Label>
                  <Input
                    id="firstName"
                    name="firstName"
                    onChange={handleInputChange}
                    placeholder="John"
                    required
                    value={newEmployee.firstName}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="lastName">Last Name *</Label>
                  <Input
                    id="lastName"
                    name="lastName"
                    onChange={handleInputChange}
                    placeholder="Doe"
                    required
                    value={newEmployee.lastName}
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Email *</Label>
                <Input
                  id="email"
                  name="email"
                  onChange={handleInputChange}
                  placeholder="john.doe@example.com"
                  required
                  type="email"
                  value={newEmployee.email}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="cellPhone">Cell Phone (Optional)</Label>
                <Input
                  id="cellPhone"
                  name="cellPhone"
                  onChange={handleInputChange}
                  placeholder="(123) 456-7890"
                  value={newEmployee.cellPhone}
                />
              </div>
            </div>

            <DialogFooter>
              <Button
                disabled={isCreating}
                onClick={createEmployee}
                type="button"
              >
                {isCreating ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Creating...
                  </>
                ) : (
                  <>
                    <Plus className="mr-2 h-4 w-4" />
                    Add Employee
                  </>
                )}
              </Button>
            </DialogFooter>
          </TabsContent>
        </Tabs>
      </DialogContent>
    </Dialog>
  );
}
