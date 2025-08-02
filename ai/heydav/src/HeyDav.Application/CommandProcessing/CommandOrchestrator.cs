using Microsoft.Extensions.Logging;
using System.Diagnostics;

namespace HeyDav.Application.CommandProcessing;

public interface ICommandOrchestrator
{
    Task<CommandResult> ProcessCommandAsync(string command, string source, Dictionary<string, object>? context = null);
    Task<CommandResult> ProcessCommandAsync(CommandRequest request);
    Task<List<CommandCapabilities>> GetAvailableCommandsAsync();
    Task<CommandResult> ProcessWithSpecificProcessorAsync(string command, string processorType, string source);
}

public class CommandOrchestrator : ICommandOrchestrator
{
    private readonly ICommandProcessorFactory _processorFactory;
    private readonly ILogger<CommandOrchestrator> _logger;

    public CommandOrchestrator(ICommandProcessorFactory processorFactory, ILogger<CommandOrchestrator> logger)
    {
        _processorFactory = processorFactory;
        _logger = logger;
    }

    public async Task<CommandResult> ProcessCommandAsync(string command, string source, Dictionary<string, object>? context = null)
    {
        var request = new CommandRequest
        {
            Command = command,
            Source = source,
            Context = context ?? new Dictionary<string, object>(),
            Timestamp = DateTime.UtcNow
        };

        return await ProcessCommandAsync(request);
    }

    public async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        var stopwatch = Stopwatch.StartNew();
        
        try
        {
            _logger.LogInformation("Processing command: '{Command}' from source: '{Source}'", 
                request.Command, request.Source);

            // Validate request
            if (string.IsNullOrWhiteSpace(request.Command))
            {
                return new CommandResult
                {
                    Success = false,
                    Message = "Command cannot be empty",
                    ProcessingTime = stopwatch.Elapsed
                };
            }

            // Get the appropriate processor
            ICommandProcessor processor;
            
            if (!string.IsNullOrEmpty(request.ProcessorType))
            {
                _logger.LogDebug("Using specified processor: {ProcessorType}", request.ProcessorType);
                processor = _processorFactory.GetProcessor(request.ProcessorType);
            }
            else
            {
                _logger.LogDebug("Finding best processor for command: {Command}", request.Command);
                processor = _processorFactory.GetBestProcessor(request.Command);
            }

            // Process the command
            var result = await processor.ProcessAsync(request);
            
            _logger.LogInformation("Command processed by {ProcessorType} in {ElapsedMs}ms. Success: {Success}", 
                processor.ProcessorType, stopwatch.ElapsedMilliseconds, result.Success);

            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error processing command: '{Command}' from source: '{Source}'", 
                request.Command, request.Source);

            return new CommandResult
            {
                Success = false,
                Message = $"An error occurred while processing your command: {ex.Message}",
                ProcessingTime = stopwatch.Elapsed,
                ProcessorUsed = "error-handler"
            };
        }
    }

    public async Task<CommandResult> ProcessWithSpecificProcessorAsync(string command, string processorType, string source)
    {
        var request = new CommandRequest
        {
            Command = command,
            Source = source,
            ProcessorType = processorType,
            Timestamp = DateTime.UtcNow
        };

        return await ProcessCommandAsync(request);
    }

    public async Task<List<CommandCapabilities>> GetAvailableCommandsAsync()
    {
        try
        {
            var processors = _processorFactory.GetAllProcessors();
            var capabilities = new List<CommandCapabilities>();

            foreach (var processor in processors)
            {
                capabilities.Add(processor.Capabilities);
            }

            _logger.LogDebug("Retrieved capabilities for {ProcessorCount} processors", capabilities.Count);
            return capabilities;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving available commands");
            return new List<CommandCapabilities>();
        }
    }
}