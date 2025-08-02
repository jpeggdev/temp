import React, { createContext, useContext, useState } from "react";
import Notification from "../components/Notification/Notification";

interface NotificationContextProps {
  showNotification: (
    message: string,
    description: string,
    type: "success" | "error",
  ) => void;
}

const NotificationContext = createContext<NotificationContextProps | null>(
  null,
);

export const useNotification = () => {
  const context = useContext(NotificationContext);
  if (!context) {
    throw new Error(
      "useNotification must be used within a NotificationProvider",
    );
  }
  return context;
};

export const NotificationProvider: React.FC<{ children: React.ReactNode }> = ({
  children,
}) => {
  const [notification, setNotification] = useState<{
    message: string;
    description: string;
    type: "success" | "error";
    show: boolean;
  }>({ message: "", description: "", type: "success", show: false });

  const showNotification = (
    message: string,
    description: string,
    type: "success" | "error",
  ) => {
    setNotification({ message, description, type, show: true });

    const DISAPPEAR_MESSAGE_IN_MILLISECONDS = 10000;
    setTimeout(() => {
      setNotification((prevNotification) => ({
        ...prevNotification,
        show: false,
      }));
    }, DISAPPEAR_MESSAGE_IN_MILLISECONDS);
  };

  return (
    <NotificationContext.Provider value={{ showNotification }}>
      {children}
      <Notification
        description={notification.description}
        message={notification.message}
        onClose={() => setNotification({ ...notification, show: false })}
        show={notification.show}
        type={notification.type}
      />
    </NotificationContext.Provider>
  );
};
