import React from "react";
import { useToast } from "@/components/ui/use-toast";
import { Button } from "@/components/ui/button";
import { cn } from "@/components/ui/lib/utils";
import { Star } from "lucide-react";

interface FavoriteButtonProps {
  isFavorited: boolean;
  onToggle: (
    event: React.MouseEvent<HTMLButtonElement>,
  ) => void | Promise<void>;
  className?: string;
}

export function FavoriteButton({
  isFavorited,
  onToggle,
  className,
}: FavoriteButtonProps) {
  const [isLoading, setIsLoading] = React.useState(false);
  const { toast } = useToast();

  const handleClick = async (e: React.MouseEvent<HTMLButtonElement>) => {
    e.preventDefault();
    e.stopPropagation();

    try {
      setIsLoading(true);
      const result = onToggle(e);
      if (result instanceof Promise) {
        await result;
      }
    } catch (error) {
      console.error("Error toggling favorite:", error);
      toast({
        title: "Error",
        description: "There was an error updating your favorites.",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Button
      className={cn(
        "h-8 w-8 rounded-full",
        isFavorited && "text-yellow-500 hover:text-yellow-600",
        className,
      )}
      disabled={isLoading}
      onClick={handleClick}
      size="icon"
      variant="ghost"
    >
      <Star className={cn("h-4 w-4", isFavorited && "fill-current")} />
      <span className="sr-only">
        {isFavorited ? "Remove from favorites" : "Add to favorites"}
      </span>
    </Button>
  );
}
