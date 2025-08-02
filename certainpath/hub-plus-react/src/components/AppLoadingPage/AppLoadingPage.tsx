import React from "react";

export default function AppLoadingPage() {
  return (
    <main className="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
      <div className="text-center">
        <p className="text-base font-semibold text-primary">Loading...</p>

        <h1 className="mt-4 text-5xl font-semibold tracking-tight text-secondary sm:text-7xl">
          Please wait
        </h1>

        <p className="mt-6 text-lg text-gray-500">
          We&apos;re getting everything ready for you.
        </p>

        <div className="mt-10 flex items-center justify-center">
          <div className="h-12 w-12 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
        </div>
      </div>
    </main>
  );
}
