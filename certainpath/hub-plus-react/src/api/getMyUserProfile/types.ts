export interface GetMyUserProfileResponse {
  data: MyUserProfile;
}

export interface MyUserProfile {
  firstName: string;
  lastName: string;
  workEmail: string;
  employeeUuid: string;
}
