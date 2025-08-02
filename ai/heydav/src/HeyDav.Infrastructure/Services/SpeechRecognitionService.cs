using Microsoft.CognitiveServices.Speech;
using Microsoft.CognitiveServices.Speech.Audio;
using System.Runtime.InteropServices;

namespace HeyDav.Infrastructure.Services;

public class SpeechRecognitionService(string subscriptionKey = "", string region = "")
    : ISpeechRecognitionService, IDisposable
{
    private SpeechRecognizer? _speechRecognizer;
    private PushAudioInputStream? _audioInputStream;
    private AudioConfig? _audioConfig;
    private SpeechRecognitionConfig _config = new();

    public event EventHandler<string>? SpeechRecognized;
    public event EventHandler<string>? PartialResultReceived;

    // For offline mode, we'll use a simple implementation
    // In production, you'd use actual Azure credentials

    public void Configure(SpeechRecognitionConfig config)
    {
        _config = config;
        InitializeRecognizer();
    }

    private void InitializeRecognizer()
    {
        try
        {
            // For demo purposes, we'll use a simple offline speech recognition
            // In production, replace with actual Azure Speech SDK configuration
            if (string.IsNullOrEmpty(subscriptionKey))
            {
                // Offline mode - simplified implementation
                return;
            }

            var speechConfig = SpeechConfig.FromSubscription(subscriptionKey, region);
            speechConfig.SpeechRecognitionLanguage = _config.Language;
            
            _audioInputStream = AudioInputStream.CreatePushStream();
            _audioConfig = AudioConfig.FromStreamInput(_audioInputStream);
            
            _speechRecognizer = new SpeechRecognizer(speechConfig, _audioConfig);
            
            _speechRecognizer.Recognizing += (s, e) =>
            {
                PartialResultReceived?.Invoke(this, e.Result.Text);
            };
            
            _speechRecognizer.Recognized += (s, e) =>
            {
                if (e.Result.Reason == ResultReason.RecognizedSpeech)
                {
                    SpeechRecognized?.Invoke(this, e.Result.Text);
                }
            };
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Failed to initialize speech recognizer: {ex.Message}");
        }
    }

    public async Task<string?> RecognizeSpeechAsync(byte[] audioData)
    {
        // Simplified offline implementation for demo
        // In production, this would use the actual speech recognition
        if (_speechRecognizer == null)
        {
            // Simulate recognition for demo purposes
            await Task.Delay(100);
            return SimulateOfflineRecognition(audioData);
        }

        _audioInputStream?.Write(audioData);
        var result = await _speechRecognizer.RecognizeOnceAsync();
        
        if (result.Reason == ResultReason.RecognizedSpeech)
        {
            return result.Text;
        }
        
        return null;
    }

    public async Task StartContinuousRecognitionAsync()
    {
        if (_speechRecognizer != null)
        {
            await _speechRecognizer.StartContinuousRecognitionAsync();
        }
    }

    public async Task StopContinuousRecognitionAsync()
    {
        if (_speechRecognizer != null)
        {
            await _speechRecognizer.StopContinuousRecognitionAsync();
        }
    }

    private string? SimulateOfflineRecognition(byte[] audioData)
    {
        // This is a placeholder for demo purposes
        // In a real implementation, you'd use an offline speech recognition library
        var random = new Random();
        var simulatedPhrases = new[]
        {
            "hey dav",
            "show my tasks",
            "add new todo",
            "check my mood",
            "what's my schedule"
        };
        
        // Simulate detection based on audio data length
        if (audioData.Length > 8000)
        {
            return simulatedPhrases[random.Next(simulatedPhrases.Length)];
        }
        
        return null;
    }

    public void Dispose()
    {
        _audioInputStream?.Dispose();
        _audioConfig?.Dispose();
        _speechRecognizer?.Dispose();
    }
}