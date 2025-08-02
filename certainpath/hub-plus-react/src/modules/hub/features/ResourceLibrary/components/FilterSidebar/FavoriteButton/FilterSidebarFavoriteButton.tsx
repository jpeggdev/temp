import React from "react";
import { Star } from "lucide-react";
import { cn } from "@/components/ui/lib/utils";
import { Switch } from "@/components/ui/switch";

interface FavoriteToggleButtonProps {
  value: boolean;
  onChange: (value: boolean) => void;
  className?: string;
}

const FavoriteToggleButton: React.FC<FavoriteToggleButtonProps> = ({
  value,
  onChange,
  className,
}) => {
  return (
    <div className={cn("flex items-center gap-2", className)}>
      <div className="flex items-center space-x-2">
        <Switch
          checked={value}
          id="favorites-only"
          onCheckedChange={onChange}
        />
        <label
          className="flex items-center gap-2 cursor-pointer text-sm font-medium text-black dark:text-gray-300"
          htmlFor="favorites-only"
        >
          <Star
            className={cn(
              "h-4 w-4",
              value ? "text-yellow-500 fill-yellow-500" : "text-black",
            )}
          />
          Favorites only
        </label>
      </div>
    </div>
  );
};

export default FavoriteToggleButton;
