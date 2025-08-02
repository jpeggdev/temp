using HeyDav.Application.CommandProcessing;
using Microsoft.Extensions.Logging;

namespace HeyDav.Infrastructure.Services;

public interface IVoiceCommandService
{
    Task StartAsync(CancellationToken cancellationToken = default);
    Task StopAsync(CancellationToken cancellationToken = default);
    bool IsRunning { get; }
    event EventHandler<VoiceCommandResult>? CommandProcessed;
}

public class VoiceCommandService : IVoiceCommandService, IDisposable
{
    private readonly IVoiceActivationService _voiceActivation;
    private readonly ICommandOrchestrator _commandOrchestrator;
    private readonly ILogger<VoiceCommandService> _logger;
    private bool _isRunning;

    public event EventHandler<VoiceCommandResult>? CommandProcessed;

    public bool IsRunning => _isRunning;

    public VoiceCommandService(
        IVoiceActivationService voiceActivation,
        ICommandOrchestrator commandOrchestrator,
        ILogger<VoiceCommandService> logger)
    {
        _voiceActivation = voiceActivation;
        _commandOrchestrator = commandOrchestrator;
        _logger = logger;

        _voiceActivation.WakeWordDetected += OnWakeWordDetected;
        _voiceActivation.CommandRecognized += OnCommandRecognized;
        _voiceActivation.StateChanged += OnStateChanged;
    }

    public async Task StartAsync(CancellationToken cancellationToken = default)
    {
        if (_isRunning)
        {
            _logger.LogWarning("Voice command service is already running");
            return;
        }

        try
        {
            await _voiceActivation.StartListeningAsync();
            _isRunning = true;
            _logger.LogInformation("Voice command service started");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to start voice command service");
            throw;
        }
    }

    public async Task StopAsync(CancellationToken cancellationToken = default)
    {
        if (!_isRunning)
        {
            _logger.LogWarning("Voice command service is not running");
            return;
        }

        try
        {
            await _voiceActivation.StopListeningAsync();
            _isRunning = false;
            _logger.LogInformation("Voice command service stopped");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error stopping voice command service");
            throw;
        }
    }

    private void OnWakeWordDetected(object? sender, string wakeWord)
    {
        _logger.LogInformation("Wake word detected: {WakeWord}", wakeWord);
        // Could add visual/audio feedback here indicating the system is listening
    }

    private async void OnCommandRecognized(object? sender, string command)
    {
        _logger.LogInformation("Voice command recognized: {Command}", command);

        try
        {
            // Process the command through the CommandOrchestrator
            var result = await _commandOrchestrator.ProcessCommandAsync(
                command, 
                "voice",
                new Dictionary<string, object>
                {
                    { "source_type", "voice" },
                    { "timestamp", DateTime.UtcNow }
                });

            var voiceResult = new VoiceCommandResult
            {
                Success = result.Success,
                Response = result.Message,
                Action = DetermineVoiceAction(result),
                Data = result.Data,
                ProcessingTime = result.ProcessingTime
            };

            CommandProcessed?.Invoke(this, voiceResult);

            _logger.LogInformation("Voice command processed successfully: {Command}", command);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error processing voice command: {Command}", command);
            
            var errorResult = new VoiceCommandResult
            {
                Success = false,
                Response = "Sorry, I encountered an error processing your command.",
                Action = VoiceCommandAction.None
            };

            CommandProcessed?.Invoke(this, errorResult);
        }
    }

    private void OnStateChanged(object? sender, VoiceActivationState state)
    {
        _logger.LogDebug("Voice activation state changed to: {State}", state);
    }

    private VoiceCommandAction DetermineVoiceAction(CommandResult result)
    {
        // Map CommandResult to VoiceCommandAction based on result properties
        if (!result.Success)
            return VoiceCommandAction.None;

        // Check processor type or result data to determine appropriate action
        return result.ProcessorUsed switch
        {
            "todo" when result.Message.Contains("created") => VoiceCommandAction.CreateNewTodo,
            "todo" when result.Message.Contains("tasks") => VoiceCommandAction.ShowTodoList,
            "goal" => VoiceCommandAction.NavigateToGoals,
            "schedule" => VoiceCommandAction.ShowSchedule,
            "help" => VoiceCommandAction.None,
            _ => VoiceCommandAction.None
        };
    }

    public void Dispose()
    {
        if (_isRunning)
        {
            StopAsync().Wait();
        }
        
        _voiceActivation.WakeWordDetected -= OnWakeWordDetected;
        _voiceActivation.CommandRecognized -= OnCommandRecognized;
        _voiceActivation.StateChanged -= OnStateChanged;
    }
}

public class VoiceCommandResult
{
    public bool Success { get; set; }
    public string Response { get; set; } = string.Empty;
    public VoiceCommandAction Action { get; set; }
    public object? Data { get; set; }
    public TimeSpan ProcessingTime { get; set; }
}

public enum VoiceCommandAction
{
    None,
    ShowTodoList,
    CreateNewTodo,
    NavigateToTodos,
    NavigateToGoals,
    NavigateToMood,
    LogMood,
    ShowSchedule
}