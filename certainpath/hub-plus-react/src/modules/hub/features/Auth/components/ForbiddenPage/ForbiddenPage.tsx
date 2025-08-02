import React from "react";
import { Link } from "react-router-dom";
import Gleap from "gleap";

export default function ForbiddenPage() {
  return (
    <main className="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
      <div className="text-center">
        <p className="text-base font-semibold text-primary">403</p>

        <h1 className="mt-4 text-5xl font-semibold tracking-tight text-secondary sm:text-7xl">
          Forbidden
        </h1>

        <p className="mt-6 text-lg text-gray-500">
          Sorry, you are not authorized to view this page.
        </p>

        <div className="mt-10 flex items-center justify-center gap-x-6">
          <Link
            className="rounded-md bg-primary px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm
                       hover:bg-primary-dark focus-visible:outline
                       focus-visible:outline-2 focus-visible:outline-offset-2
                       focus-visible:outline-primary-dark"
            to="/"
          >
            Go back home
          </Link>

          <Link
            className="text-sm font-semibold text-primary"
            onClick={() => Gleap.open()}
            to="#"
          >
            Contact support <span aria-hidden="true">&rarr;</span>
          </Link>
        </div>
      </div>
    </main>
  );
}
