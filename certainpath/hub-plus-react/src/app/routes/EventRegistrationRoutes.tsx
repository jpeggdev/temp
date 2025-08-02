import React from "react";
import { Route } from "react-router-dom";
import MainLayout from "../../components/MainLayout/MainLayout";
import { AuthenticationGuard } from "../../components/AuthenticationGuard/AuthenticationGuard";
import PermissionGuard from "@/components/PermissionGuard/PermissionGuard";
import EventCategoryList from "@/modules/eventRegistration/features/EventCategoryManagement/components/EventCategoryList/EventCategoryList";
import EventList from "@/modules/eventRegistration/features/EventManagement/components/EventList/EventList";
import CreateEvent from "@/modules/eventRegistration/features/EventManagement/components/CreateEvent/CreateEvent";
import EditEvent from "@/modules/eventRegistration/features/EventManagement/components/EditEvent/EditEvent";
import EventSessionList from "@/modules/eventRegistration/features/EventSessionManagement/components/EventSessionList/EventSessionList";
import EventDirectory from "@/modules/eventRegistration/features/EventDirectory/components/EventDirectory/EventDirectory";
import EventDetails from "@/modules/eventRegistration/features/EventDirectory/components/EventDetails/EventDetails";
import SelectAttendees from "@/modules/eventRegistration/features/EventRegistration/components/SelectAttendees/SelectAttendees";
import Checkout from "@/modules/eventRegistration/features/EventRegistration/components/Checkout/Checkout";
import EventRegistrationEntry from "@/modules/eventRegistration/features/EventRegistration/components/EventRegistrationEntry/EventRegistrationEntry";
import Confirmation from "@/modules/eventRegistration/features/EventRegistration/components/Confirmation/Confirmation";
import EventInstructorList from "@/modules/eventRegistration/features/EventInstructorManagement/components/EventInstructorList/EventInstructorList";
import VoucherList from "@/modules/eventRegistration/features/EventVoucherManagement/components/VoucherList/VoucherList";
import CreateVoucher from "@/modules/eventRegistration/features/EventVoucherManagement/components/CreateVoucher/CreateVoucher";
import VenueList from "@/modules/eventRegistration/features/EventVenueManagement/components/VenueList/VenueList";
import CreateVenue from "@/modules/eventRegistration/features/EventVenueManagement/components/CreateVenue/CreateVenue";
import EditVenue from "@/modules/eventRegistration/features/EventVenueManagement/components/EditVenue/EditVenue";
import DiscountList from "@/modules/eventRegistration/features/EventDiscountManagement/components/DiscountList/DiscountList";
import CreateDiscount from "@/modules/eventRegistration/features/EventDiscountManagement/components/CreateDiscount/CreateDiscount";
import EditDiscount from "@/modules/eventRegistration/features/EventDiscountManagement/components/EditDiscount/EditDiscount";
import EditVoucher from "@/modules/eventRegistration/features/EventVoucherManagement/components/UpdateVoucher/UpdateVoucher";
import Waitlist from "@/modules/eventRegistration/features/EventWaitlist/components/Waitlist/Waitlist";

const EventRegistrationRoutes = (
  <Route
    element={
      <AuthenticationGuard
        component={() => <MainLayout section="event-registration" />}
      />
    }
    path="/event-registration"
  >
    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EventList />
        </PermissionGuard>
      }
      path="admin/events"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EventSessionList />
        </PermissionGuard>
      }
      path="admin/events/:uuid/sessions"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <Waitlist />
        </PermissionGuard>
      }
      path="admin/events/:eventUuid/sessions/:uuid/waitlist"
    />

    <Route element={<EventDirectory />} path="events" />
    <Route element={<EventDetails />} path="events/:uuid" />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CreateEvent />
        </PermissionGuard>
      }
      path="admin/events/new"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EditEvent />
        </PermissionGuard>
      }
      path="admin/events/:uuid/edit"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EventCategoryList />
        </PermissionGuard>
      }
      path="admin/event-categories"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <VoucherList />
        </PermissionGuard>
      }
      path="admin/vouchers"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CreateVoucher />
        </PermissionGuard>
      }
      path="admin/voucher/new"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EditVoucher />
        </PermissionGuard>
      }
      path="admin/voucher/:voucherId/edit"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <VenueList />
        </PermissionGuard>
      }
      path="admin/venues"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CreateVenue />
        </PermissionGuard>
      }
      path="admin/venue/new"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EditVenue />
        </PermissionGuard>
      }
      path="admin/venue/:venueId/edit"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <DiscountList />
        </PermissionGuard>
      }
      path="admin/discounts"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <CreateDiscount />
        </PermissionGuard>
      }
      path="admin/discount/new"
    />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EditDiscount />
        </PermissionGuard>
      }
      path="admin/discount/:discountId/edit"
    />

    <Route element={<EventDirectory />} path="event-directory" />
    <Route element={<EventDetails />} path="events/:eventId" />
    <Route element={<SelectAttendees />} path="events/:eventId/attendees" />

    <Route
      element={
        <PermissionGuard requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <EventInstructorList />
        </PermissionGuard>
      }
      path="admin/event-instructors"
    />

    <Route
      element={<EventRegistrationEntry />}
      path="events/register/:eventSessionUuid/entry"
    />
    <Route
      element={<SelectAttendees />}
      path="events/register/:eventCheckoutSessionUuid/attendees"
    />
    <Route
      element={<Checkout />}
      path="events/register/:eventCheckoutSessionUuid/checkout"
    />
    <Route
      element={<Confirmation />}
      path="events/register/:eventCheckoutSessionUuid/confirmation"
    />
  </Route>
);

export default EventRegistrationRoutes;
