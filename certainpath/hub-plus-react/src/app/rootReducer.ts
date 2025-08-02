import { combineReducers } from "@reduxjs/toolkit";
import stochasticCustomersReducer from "../modules/stochastic/features/CustomerList/slices/stochasticCustomersSlice";
import stochasticProspectsReducer from "../modules/stochastic/features/ProspectList/slices/stochasticProspectsSlice";
import stochasticCampaignProductsReducer from "@/modules/stochastic/features/CampaignProductManagement/slices/campaignProductsSlice";
import userAppSettingsReducer from "../modules/hub/features/UserAppSettings/slices/userAppSettingsSlice";
import quickBooksReportsReducer from "../modules/hub/features/DocumentLibrary/slices/quickBooksReportsSlice";
import usersReducer from "../modules/hub/features/UserManagement/slices/usersSlice";
import companiesReducer from "../modules/hub/features/CompanyManagement/slices/companiesSlice";
import editUserDetailsReducer from "../modules/hub/features/UserManagement/slices/editUserDetailsSlice";
import companyProfileReducer from "../modules/hub/features/UserAppSettings/slices/companyProfileSlice";
import userProfileReducer from "../modules/hub/features/UserAppSettings/slices/userProfileSlice";
import campaignListReducer from "../modules/stochastic/features/CampaignManagement/slices/campaignListSlice";
import campaignReducer from "../modules/stochastic/features/CampaignManagement/slices/campaignSlice";
import campaignBatchListReducer from "../modules/stochastic/features/CampaignBatchManagement/slices/campaignBatchListSlice";
import batchProspectReducer from "../modules/stochastic/features/BatchProspectManagement/slices/batchProspectSlice";
import campaignFilesReducer from "../modules/stochastic/features/CampaignFileManagement/slices/campaignFilesSlice";
import createCampaignReducer from "../modules/stochastic/features/CampaignManagement/slices/createCampaignSlice";
import campaignDetailsReducer from "../modules/stochastic/features/CampaignManagement/slices/CampaignDetailsSlice";
import editRolesAndPermissionsReducer from "../modules/hub/features/UserManagement/slices/editRolesAndPermissionsSlice";
import stochasticMailingReducer from "../modules/stochastic/features/StochasticMailing/slices/stochasticMailingSlice";
import fetchRestrictedAddressesReducer from "@/modules/stochastic/features/DoNotMailManagement/slices/fetchRestrictedAddressesSlice";
import createRestrictedAddressReducer from "@/modules/stochastic/features/DoNotMailManagement/slices/createRestrictedAddressSlice";
import updateRestrictedAddressReducer from "@/modules/stochastic/features/DoNotMailManagement/slices/updateRestrictedAddressSlice";
import deleteRestrictedAddressReducer from "@/modules/stochastic/features/DoNotMailManagement/slices/deleteRestrictedAddressSlice";
import fetchSingleRestrictedAddressReducer from "@/modules/stochastic/features/DoNotMailManagement/slices/fetchSingleRestrictedAddressSlice";
import resourceManagementMetadataReducer from "@/modules/hub/features/ResourceManagement/slices/resourceManagementMetadataSlice";
import createUpdateResourceReducer from "@/modules/hub/features/ResourceManagement/slices/createUpdateResourceSlice";
import bulkBatchStatusesDetailsMetadataReducer from "../modules/stochastic/features/StochasticMailing/slices/bulkBatchStatusDetailsMetadataSlice";
import resourceListReducer from "@/modules/hub/features/ResourceManagement/slices/resourceListSlice";
import emailTemplateListReducer from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateListSlice";
import emailTemplateReducer from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateSlice";
import emailTemplateVariableListReducer from "@/modules/emailManagement/features/EmailTemplateManagement/slices/emailTemplateVariableListSlice";
import employeeRoleReducer from "@/modules/hub/features/EmployeeRoleManagement/slice/employeeRoleSlice";
import resourceCategoryReducer from "@/modules/hub/features/ResourceCategoryManagement/slice/resourceCategorySlice";
import resourceTagReducer from "@/modules/hub/features/ResourceTagManagement/slice/resourceTagSlice";
import emailTemplateCategoryReducer from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/slice/emailTemplateCategorySlice";
import eventCategoryReducer from "@/modules/eventRegistration/features/EventCategoryManagement/slice/eventCategorySlice";
import emailCampaignReducer from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignSlice";
import emailCampaignListReducer from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignListSlice";
import emailCampaignStatusesReducer from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignStatusesSlice";
import emailCampaignEventLogListReducer from "@/modules/emailManagement/features/EmailEventLogsManagement/slices/emailCampaignEventLogListSlice";
import emailCampaignEventLogsMetadataReducer from "@/modules/emailManagement/features/EmailEventLogsManagement/slices/emailCampaignEventLogsMetadataSlice";
import eventDirectoryReducer from "@/modules/eventRegistration/features/EventDirectory/slices/eventDirectorySlice";
import createUpdateEventReducer from "@/modules/eventRegistration/features/EventManagement/slices/createUpdateEventSlice";
import eventListReducer from "@/modules/eventRegistration/features/EventManagement/slices/eventListSlice";
import createUpdateEventSessionReducer from "@/modules/eventRegistration/features/EventSessionManagement/slices/createUpdateEventSessionSlice";
import eventSessionListReducer from "@/modules/eventRegistration/features/EventSessionManagement/slices/eventSessionListSlice";
import eventTagReducer from "@/modules/eventRegistration/features/EventTagManagement/slices/eventTagSlice";
import eventTypeReducer from "@/modules/eventRegistration/features/EventTypeManagement/slices/eventTypeSlice";
import wideSidebarReducer from "@/app/globalSlices/wideSidebarSlice";
import eventCheckoutReducer from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutSlice";
import eventCheckoutEntryReducer from "@/modules/eventRegistration/features/EventRegistration/slices/eventCheckoutEntrySlice";
import eventInstructorReducer from "@/modules/eventRegistration/features/EventInstructorManagement/slices/eventInstructorSlice";
import locationListReducer from "@/modules/stochastic/features/LocationsList/slices/locationListSlice";
import locationReducer from "@/modules/stochastic/features/LocationsList/slices/locationSlice";
import voucherListReducer from "@/modules/eventRegistration/features/EventVoucherManagement/slices/VoucherListSlice";
import voucherReducer from "@/modules/eventRegistration/features/EventVoucherManagement/slices/VoucherSlice";
import venueListReducer from "@/modules/eventRegistration/features/EventVenueManagement/slices/VenueListSlice";
import venueReducer from "@/modules/eventRegistration/features/EventVenueManagement/slices/VenueSlice";
import DiscountListReducer from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountListSlice";
import DiscountReducer from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountSlice";
import DiscountMetadataReducer from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountMetadataSlice";
import resourceLibraryReducer from "@/modules/hub/features/ResourceLibrary/slices/resourceLibrarySlice";
import StochasticDashboardReducer from "@/modules/stochastic/features/DashboardPage/slice/StochasticDashboardSlice";
import enrollmentsWaitlistReducer from "@/modules/eventRegistration/features/EventWaitlist/slices/enrollmentsWaitlistSlice";
import fileManagementReducer from "@/modules/hub/features/FileManagement/slices/fileManagementSlice";
import fileManagementTagReducer from "@/modules/hub/features/FileManagement/slices/fileManagementTagSlice";
import fileManagerMetadataReducer from "@/modules/hub/features/FileManagement/slices/fileManagerMetadataSlice";
import resourceLibraryMetadataReducer from "@/modules/hub/features/ResourceLibrary/slices/resourceLibraryMetadataSlice";
import DoNotMailImportReducer from "@/modules/stochastic/features/DoNotMailImport/slice/DoNotMailListImportSlice";

const rootReducer = combineReducers({
  stochasticCustomers: stochasticCustomersReducer,
  stochasticProspects: stochasticProspectsReducer,
  stochasticCampaignProducts: stochasticCampaignProductsReducer,
  userAppSettings: userAppSettingsReducer,
  quickBooksReports: quickBooksReportsReducer,
  users: usersReducer,
  editUserDetails: editUserDetailsReducer,
  companies: companiesReducer,
  companyProfile: companyProfileReducer,
  userProfile: userProfileReducer,
  campaignList: campaignListReducer,
  campaign: campaignReducer,
  campaignBatchList: campaignBatchListReducer,
  batchProspect: batchProspectReducer,
  campaignFiles: campaignFilesReducer,
  createCampaign: createCampaignReducer,
  campaignDetails: campaignDetailsReducer,
  emailTemplate: emailTemplateReducer,
  emailTemplateList: emailTemplateListReducer,
  emailTemplateVariableList: emailTemplateVariableListReducer,
  editRolesAndPermissions: editRolesAndPermissionsReducer,
  stochasticMailing: stochasticMailingReducer,
  fetchRestrictedAddresses: fetchRestrictedAddressesReducer,
  createRestrictedAddress: createRestrictedAddressReducer,
  updateRestrictedAddress: updateRestrictedAddressReducer,
  deleteRestrictedAddress: deleteRestrictedAddressReducer,
  fetchSingleRestrictedAddress: fetchSingleRestrictedAddressReducer,
  resourceManagementMetadataReducer: resourceManagementMetadataReducer,
  createUpdateResource: createUpdateResourceReducer,
  resourceCategory: resourceCategoryReducer,
  resourceTag: resourceTagReducer,
  bulkBatchStatusDetailsMetadata: bulkBatchStatusesDetailsMetadataReducer,
  resourceList: resourceListReducer,
  employeeRole: employeeRoleReducer,
  emailTemplateCategory: emailTemplateCategoryReducer,
  eventCategory: eventCategoryReducer,
  emailCampaign: emailCampaignReducer,
  emailCampaignList: emailCampaignListReducer,
  emailCampaignStatuses: emailCampaignStatusesReducer,
  emailCampaignEventLogList: emailCampaignEventLogListReducer,
  emailCampaignEventLogsMetadata: emailCampaignEventLogsMetadataReducer,
  eventDirectory: eventDirectoryReducer,
  createUpdateEvent: createUpdateEventReducer,
  eventList: eventListReducer,
  createUpdateEventSession: createUpdateEventSessionReducer,
  eventSessionList: eventSessionListReducer,
  eventTag: eventTagReducer,
  eventType: eventTypeReducer,
  wideSidebar: wideSidebarReducer,
  eventCheckout: eventCheckoutReducer,
  eventCheckoutEntry: eventCheckoutEntryReducer,
  eventInstructor: eventInstructorReducer,
  locationList: locationListReducer,
  location: locationReducer,
  voucherList: voucherListReducer,
  voucher: voucherReducer,
  venueList: venueListReducer,
  venue: venueReducer,
  discountList: DiscountListReducer,
  discount: DiscountReducer,
  discountMetadata: DiscountMetadataReducer,
  resourceLibrary: resourceLibraryReducer,
  stochasticDashboard: StochasticDashboardReducer,
  enrollmentsWaitlist: enrollmentsWaitlistReducer,
  fileManagement: fileManagementReducer,
  fileManagementTag: fileManagementTagReducer,
  fileManagerMetadata: fileManagerMetadataReducer,
  resourceLibraryMetadata: resourceLibraryMetadataReducer,
  doNotMailImport: DoNotMailImportReducer,
});

export type RootState = ReturnType<typeof rootReducer>;

export default rootReducer;
