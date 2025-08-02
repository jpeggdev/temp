namespace HeyDav.Infrastructure.Services;

public interface IVoiceActivationService
{
    event EventHandler<string>? WakeWordDetected;
    event EventHandler<string>? CommandRecognized;
    event EventHandler<VoiceActivationState>? StateChanged;
    
    VoiceActivationState CurrentState { get; }
    bool IsListening { get; }
    
    Task StartListeningAsync();
    Task StopListeningAsync();
    void ProcessAudioBuffer(byte[] audioData, int bytesRecorded);
}

public enum VoiceActivationState
{
    Idle,
    ListeningForWakeWord,
    ListeningForCommand,
    ProcessingCommand,
    Error
}