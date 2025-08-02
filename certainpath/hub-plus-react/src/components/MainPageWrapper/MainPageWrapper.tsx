import React from "react";
import { Helmet } from "react-helmet-async";
import LoadingIndicator from "../LoadingIndicator/LoadingIndicator";
import Breadcrumbs, {
  ManualBreadcrumb,
} from "@/components/Breadcrumbs/Breadcrumbs";

interface PageWrapperProps {
  title?: string;
  subtitle?: string;
  loading?: boolean;
  error?: string | null;
  children: React.ReactNode;
  actions?: React.ReactNode;
  manualBreadcrumbs?: ManualBreadcrumb[];
  hideBreadcrumbs?: boolean;
  hideHeader?: boolean;
  titleHelpIcon?: React.ReactNode;
}

const MainPageWrapper: React.FC<PageWrapperProps> = ({
  title,
  subtitle,
  loading,
  children,
  actions,
  manualBreadcrumbs,
  hideBreadcrumbs,
  hideHeader,
  titleHelpIcon,
}) => {
  const pageTitle = title || "Certain Path";
  const showHeaderBlock = (!!title || !!subtitle) && !hideHeader;

  return (
    <div className="relative">
      {!hideBreadcrumbs && <Breadcrumbs manualCrumbs={manualBreadcrumbs} />}

      <Helmet>
        <title>{pageTitle}</title>
      </Helmet>

      <div className="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        {showHeaderBlock && (
          <div className="pb-6">
            <div className="mb-2 flex items-center justify-between">
              {!hideHeader && title && (
                <h1 className="flex items-center gap-2 text-3xl font-bold text-gray-900 dark:text-white">
                  {title}
                  {titleHelpIcon && <span>{titleHelpIcon}</span>}
                </h1>
              )}
              {actions}
            </div>
            {subtitle && (
              <p className="text-gray-600 dark:text-gray-400">{subtitle}</p>
            )}
          </div>
        )}

        <div>{children}</div>
      </div>

      {loading && (
        <div className="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
          <LoadingIndicator />
        </div>
      )}
    </div>
  );
};

export default MainPageWrapper;
