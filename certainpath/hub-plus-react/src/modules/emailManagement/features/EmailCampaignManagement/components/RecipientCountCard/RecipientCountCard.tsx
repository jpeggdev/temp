import { UsersIcon } from "@heroicons/react/24/outline";
import { cn } from "@/utils/utils";

const RecipientCountCard = ({
  count,
  message,
}: {
  count?: number;
  message?: string;
}) => {
  const isDisplayCount = count !== undefined;
  const textColor = isDisplayCount ? "text-black" : "text-gray-500";
  const bgColor = isDisplayCount ? "bg-background" : "bg-gray-200";
  const displayText = isDisplayCount ? count : message;

  return (
    <div
      className={cn(
        "w-full min-h-[40px] rounded-lg border border-input px-3 py-2 shadow-sm mb-2",
        "text-sm md:text-sm",
        bgColor,
      )}
    >
      {isDisplayCount ? (
        <div className="flex justify-between items-center">
          <div className="flex items-center gap-2">
            <UsersIcon className={cn("h-4 w-4 md:h-5 md:w-5", textColor)} />
            <span className={cn("font-medium", textColor)}>
              Recipient Count
            </span>
          </div>
          <span className={cn("ml-4", textColor)}>{displayText}</span>
        </div>
      ) : (
        <div className="flex flex-col md:flex-row md:justify-between md:items-center">
          <div className="flex items-center gap-2">
            <UsersIcon className={cn("h-4 w-4 md:h-5 md:w-5", textColor)} />
            <span className={cn("font-medium", textColor)}>
              Recipient Count
            </span>
          </div>
          <span className={cn("mt-1 md:mt-0 md:ml-4", textColor)}>
            {displayText}
          </span>
        </div>
      )}
    </div>
  );
};

export default RecipientCountCard;
