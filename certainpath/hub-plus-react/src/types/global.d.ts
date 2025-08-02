interface Window {
  SessionRewindInitialized?: boolean;
  SessionRewindConfig?: {
    apiKey: string;
    startRecording: boolean;
    createNewSession?: boolean;
    sessionInfo?: {
      customFieldA?: string;
      customFieldB?: number;
    };
    userInfo?: {
      userId: string;
      userName: string;
    };
    onLoad?: () => void;
  };
}
