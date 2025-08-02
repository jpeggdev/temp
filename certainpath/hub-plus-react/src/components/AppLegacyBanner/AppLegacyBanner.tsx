import React, { useState } from "react";
import { Switch } from "@/components/ui/switch";

function AppLegacyBanner() {
  const [bannerEnabled, setBannerEnabled] = useState(false);

  const handleBannerToggle = (enabled: boolean) => {
    setBannerEnabled(enabled);
    if (enabled) {
      window.location.href = "https://www.mycertainpathhub.com/";
    }
  };

  return (
    <div
      className="
        sticky top-0 z-10
        flex h-16 shrink-0 items-center
        px-4 shadow-sm
        sm:gap-x-6 sm:px-6 lg:px-8
        bg-gradient-to-r from-blue-500 to-purple-600 text-white
      "
    >
      <span className="font-semibold flex items-center">
        Beta Hub
        <span className="bg-yellow-300 text-black text-xs font-semibold rounded-full py-0.5 px-2 ml-2">
          Beta
        </span>
      </span>
      <div className="ml-auto flex items-center gap-x-2">
        <span className="text-sm">Using Beta Hub (Limited Features)</span>
        <Switch
          checked={bannerEnabled}
          className="data-[state=checked]:bg-gray-200"
          onCheckedChange={handleBannerToggle}
        />
      </div>
    </div>
  );
}

export default AppLegacyBanner;
