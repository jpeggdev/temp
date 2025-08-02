import React from "react";
import { ChevronRight, Home, Folder } from "lucide-react";
import { BreadcrumbItem } from "../../api/listFolderContents/types";
import styles from "./Breadcrumbs.module.css";

interface BreadcrumbsProps {
  breadcrumbs: BreadcrumbItem[];
  onNavigate: (folderUuid: string | null) => void;
}

const Breadcrumbs: React.FC<BreadcrumbsProps> = ({
  breadcrumbs,
  onNavigate,
}) => {
  return (
    <nav aria-label="breadcrumb" className={styles.breadcrumbsContainer}>
      <div className={styles.breadcrumbsList}>
        <button
          className={`${styles.breadcrumbItem} ${styles.homeItem}`}
          onClick={() => onNavigate(null)}
          title="Home"
        >
          <Home className={styles.homeIcon} size={16} />
          <span>Home</span>
        </button>

        {breadcrumbs.length > 0 && (
          <ChevronRight className={styles.separator} size={16} />
        )}

        {breadcrumbs.map((breadcrumb, index) => (
          <React.Fragment key={breadcrumb.uuid}>
            <button
              className={`${styles.breadcrumbItem} ${
                index === breadcrumbs.length - 1 ? styles.currentItem : ""
              }`}
              onClick={() => onNavigate(breadcrumb.uuid)}
              title={breadcrumb.name}
            >
              {index === breadcrumbs.length - 1 && (
                <Folder className={styles.folderIcon} size={14} />
              )}
              <span className={styles.breadcrumbText}>{breadcrumb.name}</span>
            </button>
            {index < breadcrumbs.length - 1 && (
              <ChevronRight className={styles.separator} size={16} />
            )}
          </React.Fragment>
        ))}
      </div>
    </nav>
  );
};

export default Breadcrumbs;
