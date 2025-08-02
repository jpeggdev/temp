"use client";

import React from "react";

const Security: React.FC = () => {
  return (
    <div className="border-b border-gray-900/10 pb-12">
      <h2 className="text-base font-semibold leading-7 text-gray-900">
        Security
      </h2>
      <p className="mt-1 text-sm leading-6 text-gray-600">
        Update your security settings and manage your account access.
      </p>

      {/* Since we're leaving this component dumb, no state or handlers are added */}
      <form>
        <div className="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
          {/* New Password */}
          <div className="col-span-full">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="newPassword"
            >
              New Password
            </label>
            <div className="mt-2">
              <input
                autoComplete="new-password"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                id="newPassword"
                name="newPassword"
                type="password"
              />
            </div>
          </div>

          {/* Confirm Password */}
          <div className="col-span-full">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="confirmPassword"
            >
              Confirm Password
            </label>
            <div className="mt-2">
              <input
                autoComplete="new-password"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                id="confirmPassword"
                name="confirmPassword"
                type="password"
              />
            </div>
          </div>

          {/* Email */}
          <div className="sm:col-span-4">
            <label
              className="block text-sm font-medium leading-6 text-gray-900"
              htmlFor="securityEmail"
            >
              Email
            </label>
            <div className="mt-2">
              <input
                autoComplete="email"
                className="block w-full rounded-md border p-2 text-gray-900 shadow-sm focus:ring-indigo-600 sm:text-sm sm:leading-6"
                id="securityEmail"
                name="securityEmail"
                placeholder="your-email@example.com"
                type="email"
              />
            </div>
          </div>
        </div>

        {/* Save Button */}
        <div className="mt-6 flex items-center justify-end gap-x-6">
          <button
            className="text-sm font-semibold leading-6 text-gray-900"
            type="button"
          >
            Cancel
          </button>
          <button
            className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:ring-indigo-600"
            type="submit"
          >
            Save
          </button>
        </div>
      </form>
    </div>
  );
};

export default Security;
