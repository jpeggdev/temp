using NAudio.Wave;
using System.Collections.Concurrent;

namespace HeyDav.Infrastructure.Services;

public class VoiceActivationService : IVoiceActivationService, IDisposable
{
    private readonly ISpeechRecognitionService _speechRecognition;
    private readonly WakeWordDetector _wakeWordDetector;
    private WaveInEvent? _waveIn;
    private VoiceActivationState _currentState = VoiceActivationState.Idle;
    private DateTime _lastWakeWordTime = DateTime.MinValue;
    private readonly TimeSpan _commandTimeout = TimeSpan.FromSeconds(5);
    private readonly ConcurrentQueue<byte[]> _commandAudioBuffer = new();
    private CancellationTokenSource? _commandTimeoutCts;

    public event EventHandler<string>? WakeWordDetected;
    public event EventHandler<string>? CommandRecognized;
    public event EventHandler<VoiceActivationState>? StateChanged;

    public VoiceActivationState CurrentState
    {
        get => _currentState;
        private set
        {
            if (_currentState != value)
            {
                _currentState = value;
                StateChanged?.Invoke(this, value);
            }
        }
    }

    public bool IsListening => _waveIn != null && CurrentState != VoiceActivationState.Idle;

    public VoiceActivationService(ISpeechRecognitionService speechRecognition)
    {
        _speechRecognition = speechRecognition;
        _wakeWordDetector = new WakeWordDetector(speechRecognition);
        _wakeWordDetector.WakeWordDetected += OnWakeWordDetected;
        _speechRecognition.SpeechRecognized += OnSpeechRecognized;
    }

    public Task StartListeningAsync()
    {
        if (_waveIn != null) return Task.CompletedTask;

        try
        {
            _waveIn = new WaveInEvent
            {
                WaveFormat = new WaveFormat(16000, 16, 1), // 16kHz, 16-bit, mono
                BufferMilliseconds = 100
            };

            _waveIn.DataAvailable += OnAudioDataAvailable;
            _waveIn.RecordingStopped += OnRecordingStopped;

            _waveIn.StartRecording();
            _wakeWordDetector.StartDetection();
            CurrentState = VoiceActivationState.ListeningForWakeWord;
        }
        catch (Exception ex)
        {
            CurrentState = VoiceActivationState.Error;
            throw new InvalidOperationException("Failed to start voice activation", ex);
        }

        return Task.CompletedTask;
    }

    public Task StopListeningAsync()
    {
        _waveIn?.StopRecording();
        _wakeWordDetector.StopDetection();
        _commandTimeoutCts?.Cancel();
        CurrentState = VoiceActivationState.Idle;
        return Task.CompletedTask;
    }

    private void OnAudioDataAvailable(object? sender, WaveInEventArgs e)
    {
        if (e.BytesRecorded > 0)
        {
            var audioData = new byte[e.BytesRecorded];
            Array.Copy(e.Buffer, audioData, e.BytesRecorded);
            ProcessAudioBuffer(audioData, e.BytesRecorded);
        }
    }

    public void ProcessAudioBuffer(byte[] audioData, int bytesRecorded)
    {
        switch (CurrentState)
        {
            case VoiceActivationState.ListeningForWakeWord:
                _wakeWordDetector.AddAudioData(audioData);
                break;
                
            case VoiceActivationState.ListeningForCommand:
                _commandAudioBuffer.Enqueue(audioData);
                break;
        }
    }

    private async void OnWakeWordDetected(object? sender, string wakeWord)
    {
        _lastWakeWordTime = DateTime.UtcNow;
        CurrentState = VoiceActivationState.ListeningForCommand;
        WakeWordDetected?.Invoke(this, wakeWord);
        
        // Stop wake word detection temporarily
        _wakeWordDetector.StopDetection();
        
        // Start command timeout
        _commandTimeoutCts?.Cancel();
        _commandTimeoutCts = new CancellationTokenSource();
        
        _ = Task.Run(async () =>
        {
            try
            {
                await Task.Delay(_commandTimeout, _commandTimeoutCts.Token);
                await ResetToWakeWordListening();
            }
            catch (TaskCanceledException)
            {
                // Timeout was cancelled, command was recognized
            }
        });

        // Start processing command audio
        await ProcessCommandAudioAsync();
    }

    private async Task ProcessCommandAudioAsync()
    {
        var commandBuffer = new List<byte>();
        var silenceCount = 0;
        const int silenceThreshold = 10; // ~1 second of silence

        while (CurrentState == VoiceActivationState.ListeningForCommand)
        {
            if (_commandAudioBuffer.TryDequeue(out var audioData))
            {
                commandBuffer.AddRange(audioData);
                
                // Simple silence detection (check for low amplitude)
                if (IsBufferSilent(audioData))
                {
                    silenceCount++;
                    if (silenceCount >= silenceThreshold && commandBuffer.Count > 0)
                    {
                        // Process the command
                        CurrentState = VoiceActivationState.ProcessingCommand;
                        var command = await _speechRecognition.RecognizeSpeechAsync(commandBuffer.ToArray());
                        
                        if (!string.IsNullOrEmpty(command))
                        {
                            CommandRecognized?.Invoke(this, command);
                        }
                        
                        await ResetToWakeWordListening();
                        break;
                    }
                }
                else
                {
                    silenceCount = 0;
                }
            }
            else
            {
                await Task.Delay(10);
            }
        }
    }

    private bool IsBufferSilent(byte[] buffer)
    {
        // Simple amplitude check for silence
        const int silenceThreshold = 500;
        var sum = 0L;
        
        for (int i = 0; i < buffer.Length; i += 2)
        {
            if (i + 1 < buffer.Length)
            {
                var sample = BitConverter.ToInt16(buffer, i);
                sum += Math.Abs(sample);
            }
        }
        
        var average = sum / (buffer.Length / 2);
        return average < silenceThreshold;
    }

    private async Task ResetToWakeWordListening()
    {
        _commandTimeoutCts?.Cancel();
        
        // Clear command buffer
        while (_commandAudioBuffer.TryDequeue(out _)) { }
        
        // Resume wake word detection
        _wakeWordDetector.StartDetection();
        CurrentState = VoiceActivationState.ListeningForWakeWord;
        
        await Task.CompletedTask;
    }

    private void OnSpeechRecognized(object? sender, string recognizedText)
    {
        if (CurrentState == VoiceActivationState.ListeningForCommand)
        {
            CommandRecognized?.Invoke(this, recognizedText);
            _ = ResetToWakeWordListening();
        }
    }

    private void OnRecordingStopped(object? sender, StoppedEventArgs e)
    {
        if (e.Exception != null)
        {
            CurrentState = VoiceActivationState.Error;
            Console.WriteLine($"Recording stopped with error: {e.Exception.Message}");
        }
    }

    public void Dispose()
    {
        StopListeningAsync().Wait();
        _waveIn?.Dispose();
        _wakeWordDetector.Dispose();
        _commandTimeoutCts?.Dispose();
    }
}