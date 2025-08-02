import { RootState } from "../../../../../app/rootReducer";

export const selectUserAppSettings = (state: RootState) =>
  state.userAppSettings.userAppSettings;

export const selectActiveSessionCompanyName = (
  state: RootState,
): string | null => {
  const userAppSettings = selectUserAppSettings(state);

  if (!userAppSettings) return null;

  return userAppSettings.companyName ?? null;
};

export const selectIntacctId = (state: RootState): string | null => {
  const userAppSettings = selectUserAppSettings(state);

  if (!userAppSettings) return null;

  return userAppSettings.intacctId ?? null;
};

export const selectEmployeeUuid = (state: RootState): string | null => {
  const userAppSettings = selectUserAppSettings(state);

  if (!userAppSettings) return null;

  return userAppSettings.employeeUuid ?? null;
};
