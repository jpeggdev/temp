import React from "react";
import { Link, useLocation } from "react-router-dom";
import { buildBreadcrumbChain } from "@/navigation/breadcrumbConfig";

export interface ManualBreadcrumb {
  path: string;
  label: string;
  clickable?: boolean;
}

interface BreadcrumbsProps {
  manualCrumbs?: ManualBreadcrumb[];
}

const Breadcrumbs: React.FC<BreadcrumbsProps> = ({ manualCrumbs }) => {
  const location = useLocation();
  const crumbs = manualCrumbs || buildBreadcrumbChain(location.pathname);

  if (crumbs.length === 0) return null;

  return (
    <nav
      aria-label="Breadcrumb"
      className="border-b border-gray-200 bg-white overflow-x-auto"
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol
          className="flex py-2 items-center space-x-4 flex-nowrap"
          role="list"
        >
          {crumbs.map((crumb, index) => {
            if (crumb.path === "/") return null;

            const isLast = index === crumbs.length - 1;
            const clickable =
              manualCrumbs && manualCrumbs[index].clickable !== undefined
                ? manualCrumbs[index].clickable
                : !isLast;

            return (
              <li
                className="inline-flex items-center"
                key={`${crumb.path}-${index}`}
              >
                {index > 0 && (
                  <svg
                    aria-hidden="true"
                    className="mx-2 h-4 w-4 text-gray-300 flex-shrink-0"
                    fill="currentColor"
                    viewBox="0 0 24 44"
                  >
                    <path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
                  </svg>
                )}

                {clickable ? (
                  <Link
                    className="whitespace-nowrap text-sm font-medium text-gray-500 hover:text-gray-700"
                    to={crumb.path}
                  >
                    {crumb.label}
                  </Link>
                ) : (
                  <span
                    aria-current="page"
                    className="whitespace-nowrap text-sm font-medium text-gray-500"
                  >
                    {crumb.label}
                  </span>
                )}
              </li>
            );
          })}
        </ol>
      </div>
    </nav>
  );
};

export default Breadcrumbs;
