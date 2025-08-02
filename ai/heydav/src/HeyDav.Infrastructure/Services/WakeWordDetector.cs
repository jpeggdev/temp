using NAudio.Wave;
using System.Collections.Concurrent;

namespace HeyDav.Infrastructure.Services;

public class WakeWordDetector : IDisposable
{
    private readonly string[] _wakeWords = { "hey dav", "a dav" };
    private readonly ISpeechRecognitionService _speechRecognition;
    private readonly ConcurrentQueue<byte[]> _audioQueue = new();
    private readonly CancellationTokenSource _cancellationTokenSource = new();
    private Task? _processingTask;
    private bool _isDetecting;

    public event EventHandler<string>? WakeWordDetected;

    public WakeWordDetector(ISpeechRecognitionService speechRecognition)
    {
        _speechRecognition = speechRecognition;
        _speechRecognition.SpeechRecognized += OnSpeechRecognized;
    }

    public void StartDetection()
    {
        if (_isDetecting) return;
        
        _isDetecting = true;
        _processingTask = Task.Run(ProcessAudioQueueAsync, _cancellationTokenSource.Token);
    }

    public void StopDetection()
    {
        _isDetecting = false;
        _cancellationTokenSource.Cancel();
        _processingTask?.Wait(TimeSpan.FromSeconds(1));
    }

    public void AddAudioData(byte[] audioData)
    {
        if (_isDetecting)
        {
            _audioQueue.Enqueue(audioData);
        }
    }

    private async Task ProcessAudioQueueAsync()
    {
        var buffer = new List<byte>();
        var lastProcessTime = DateTime.UtcNow;
        
        while (!_cancellationTokenSource.Token.IsCancellationRequested)
        {
            if (_audioQueue.TryDequeue(out var audioData))
            {
                buffer.AddRange(audioData);
                
                // Process buffer when it reaches a certain size or timeout
                if (buffer.Count >= 16000 * 2 || // 1 second of 16kHz audio
                    (DateTime.UtcNow - lastProcessTime).TotalMilliseconds > 500)
                {
                    await ProcessBufferForWakeWord(buffer.ToArray());
                    buffer.Clear();
                    lastProcessTime = DateTime.UtcNow;
                }
            }
            else
            {
                await Task.Delay(10, _cancellationTokenSource.Token);
            }
        }
    }

    private async Task ProcessBufferForWakeWord(byte[] audioBuffer)
    {
        try
        {
            var result = await _speechRecognition.RecognizeSpeechAsync(audioBuffer);
            if (!string.IsNullOrEmpty(result))
            {
                CheckForWakeWord(result);
            }
        }
        catch (Exception ex)
        {
            // Log error
            Console.WriteLine($"Error processing audio for wake word: {ex.Message}");
        }
    }

    private void OnSpeechRecognized(object? sender, string recognizedText)
    {
        CheckForWakeWord(recognizedText);
    }

    private void CheckForWakeWord(string text)
    {
        var lowerText = text.ToLowerInvariant();
        
        foreach (var wakeWord in _wakeWords)
        {
            if (lowerText.Contains(wakeWord))
            {
                WakeWordDetected?.Invoke(this, wakeWord);
                break;
            }
        }
    }

    public void Dispose()
    {
        StopDetection();
        _cancellationTokenSource.Dispose();
        _speechRecognition.SpeechRecognized -= OnSpeechRecognized;
    }
}