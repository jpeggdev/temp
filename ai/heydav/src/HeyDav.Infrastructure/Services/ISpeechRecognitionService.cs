namespace HeyDav.Infrastructure.Services;

public interface ISpeechRecognitionService
{
    event EventHandler<string>? SpeechRecognized;
    event EventHandler<string>? PartialResultReceived;
    
    Task<string?> RecognizeSpeechAsync(byte[] audioData);
    Task StartContinuousRecognitionAsync();
    Task StopContinuousRecognitionAsync();
    void Configure(SpeechRecognitionConfig config);
}

public class SpeechRecognitionConfig
{
    public string Language { get; set; } = "en-US";
    public double SilenceTimeoutSeconds { get; set; } = 2.0;
    public bool EnableProfanityFilter { get; set; } = true;
    public bool EnableDictation { get; set; } = false;
}