import React from "react";
import { ReactNode } from "react";
import Footer from "@/components/Footer/Footer";

interface MainContentProps {
  children: ReactNode;
}

const MainContent: React.FC<MainContentProps> = ({ children }) => (
  <div className="h-full flex flex-col">
    <main className="flex-1">
      <div>{children}</div>
    </main>
    <Footer />
  </div>
);

export default MainContent;
