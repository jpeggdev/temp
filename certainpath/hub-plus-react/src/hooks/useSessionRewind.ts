import { useEffect } from "react";

const useSessionRewind = () => {
  useEffect(() => {
    if (window.SessionRewindInitialized) {
      return;
    }

    const sessionRewindConfig: Window["SessionRewindConfig"] = {
      apiKey: process.env.REACT_APP_SESSION_REWIND_API_KEY || "",
      startRecording: true,
      // OPTIONAL: Uncomment and modify the following fields as needed
      // createNewSession: false,
      // sessionInfo: { customFieldA: "This is a custom field" },
      // userInfo: { userId: "hello@sessionrewind.com", userName: "John Doe" },
      // onLoad: () => {
      //   window.sessionRewind.getSessionUrl((url) => { console.log(url); });
      // },
    };

    window.SessionRewindConfig = sessionRewindConfig;
    window.SessionRewindInitialized = true;

    const script = document.createElement("script");
    script.async = true;
    script.crossOrigin = "anonymous";
    script.src = "https://rec.sessionrewind.com/srloader.js";

    document.head.appendChild(script);

    return () => {
      document.head.removeChild(script);
    };
  }, []);
};

export default useSessionRewind;
