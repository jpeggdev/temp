import React from "react";
import styles from "./LoadinIndicator.module.css";
import { useTheme } from "../../context/ThemeContext";

interface LoadingIndicatorProps {
  isFullScreen?: boolean; // Optional prop to control min-h-screen
}

const LoadingIndicator: React.FC<LoadingIndicatorProps> = ({
  isFullScreen = true,
}) => {
  const { theme } = useTheme();
  const loaderColor = theme === "dark" ? "#17084A" : "#B21E34";

  return (
    <div
      className={`flex justify-center items-center ${isFullScreen ? "min-h-screen" : ""}`}
    >
      <div className={styles.growingBars}>
        <div
          className={styles.bar}
          style={{ backgroundColor: loaderColor }}
        ></div>
        <div
          className={styles.bar}
          style={{ backgroundColor: loaderColor }}
        ></div>
        <div
          className={styles.bar}
          style={{ backgroundColor: loaderColor }}
        ></div>
        <div
          className={styles.bar}
          style={{ backgroundColor: loaderColor }}
        ></div>
      </div>
    </div>
  );
};

export default LoadingIndicator;
