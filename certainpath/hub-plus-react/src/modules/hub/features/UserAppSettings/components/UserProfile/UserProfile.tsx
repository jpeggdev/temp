"use client";

import React, { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";

import { AppDispatch } from "../../../../../../app/store";
import { RootState } from "../../../../../../app/rootReducer";
import { MyUserProfile } from "../../../../../../api/getMyUserProfile/types";
import {
  fetchUserProfileAction,
  updateUserProfileAction,
} from "../../slices/userProfileSlice";
import { useNotification } from "../../../../../../context/NotificationContext";

const UserProfile: React.FC = () => {
  const dispatch = useDispatch<AppDispatch>();
  const { showNotification } = useNotification();

  // Local state for form inputs
  const [formData, setFormData] = useState<MyUserProfile>({
    firstName: "",
    lastName: "",
    workEmail: "",
    employeeUuid: "",
  });

  const { userProfile, loading, saving } = useSelector(
    (state: RootState) => state.userProfile,
  );

  useEffect(() => {
    dispatch(fetchUserProfileAction());
  }, [dispatch]);

  useEffect(() => {
    if (userProfile) {
      setFormData(userProfile);
    }
  }, [userProfile]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }));
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    dispatch(
      updateUserProfileAction(formData, () => {
        showNotification(
          "Successfully updated user profile!",
          "Your user profile information has been updated.",
          "success",
        );
      }),
    );
  };

  return (
    <div className="border-b border-gray-900/10 pb-12">
      <h2 className="text-base font-semibold leading-7 text-gray-900">
        User Profile
      </h2>
      <p className="mt-1 text-sm leading-6 text-gray-600">
        This information will be displayed publicly so be careful what you
        share.
      </p>

      <form onSubmit={handleSubmit}>
        <div className="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
          {/* First Name */}
          <div className="sm:col-span-3">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="firstName"
            >
              First Name
            </label>
            <div className="mt-2">
              <input
                autoComplete="given-name"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                id="firstName"
                name="firstName"
                onChange={handleChange}
                type="text"
                value={formData.firstName}
              />
            </div>
          </div>

          {/* Last Name */}
          <div className="sm:col-span-3">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="lastName"
            >
              Last Name
            </label>
            <div className="mt-2">
              <input
                autoComplete="family-name"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                id="lastName"
                name="lastName"
                onChange={handleChange}
                type="text"
                value={formData.lastName}
              />
            </div>
          </div>

          {/* Work Email */}
          <div className="sm:col-span-4">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="workEmail"
            >
              Work Email
            </label>
            <div className="mt-2">
              <input
                autoComplete="email"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                id="workEmail"
                name="workEmail"
                onChange={handleChange}
                type="email"
                value={formData.workEmail}
              />
            </div>
          </div>
        </div>

        {/* Save Button */}
        <div className="mt-6 flex items-center justify-end gap-x-6">
          <button
            className="text-sm font-semibold leading-6 text-gray-900"
            disabled={loading || saving}
            onClick={() => setFormData(userProfile || formData)}
            type="button"
          >
            Cancel
          </button>
          <button
            className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:ring-indigo-600"
            disabled={saving}
            type="submit"
          >
            {saving ? "Saving..." : "Save"}
          </button>
        </div>
      </form>
    </div>
  );
};

export default UserProfile;
