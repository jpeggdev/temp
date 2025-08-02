import React from "react";
import { Button } from "@/components/ui/button";
import { Filter } from "lucide-react";
import { useIsMobile } from "@/hooks/use-mobile";

interface MobileFilterToggleButtonProps {
  onClick: () => void;
}

const MobileFilterToggleButton: React.FC<MobileFilterToggleButtonProps> = ({
  onClick,
}) => {
  const isMobile = useIsMobile();

  if (!isMobile) return null;

  return (
    <div className="px-4 pt-4">
      <Button
        aria-label="Open filters"
        className="mb-4 p-2 h-10 w-10"
        onClick={onClick}
        variant="outline"
      >
        <Filter className="w-5 h-5" />
      </Button>
    </div>
  );
};

export default MobileFilterToggleButton;
