using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;
using HeyDav.Application.CommandProcessing.Processors;

namespace HeyDav.Application.CommandProcessing;

public class CommandProcessorFactory : ICommandProcessorFactory
{
    private readonly IServiceProvider _serviceProvider;
    private readonly ILogger<CommandProcessorFactory> _logger;
    private readonly Dictionary<string, Type> _processors = new();

    public CommandProcessorFactory(IServiceProvider serviceProvider, ILogger<CommandProcessorFactory> logger)
    {
        _serviceProvider = serviceProvider;
        _logger = logger;
        RegisterBuiltInProcessors();
    }

    private void RegisterBuiltInProcessors()
    {
        _processors["todo"] = typeof(TodoCommandProcessor);
        _processors["goal"] = typeof(GoalCommandProcessor);
        _processors["schedule"] = typeof(ScheduleCommandProcessor);
        _processors["general"] = typeof(GeneralCommandProcessor);
        _processors["help"] = typeof(HelpCommandProcessor);
        _processors["system"] = typeof(SystemCommandProcessor);
    }

    public ICommandProcessor GetProcessor(string processorType)
    {
        if (_processors.TryGetValue(processorType.ToLowerInvariant(), out var processorTypeInfo))
        {
            var processor = _serviceProvider.GetService(processorTypeInfo) as ICommandProcessor;
            if (processor != null)
            {
                return processor;
            }
        }

        _logger.LogWarning("Processor type '{ProcessorType}' not found, falling back to general processor", processorType);
        return GetProcessor("general");
    }

    public ICommandProcessor GetBestProcessor(string command)
    {
        var normalizedCommand = command.ToLowerInvariant().Trim();

        // Get all registered processors and find the best match
        var allProcessors = GetAllProcessors().ToList();
        
        foreach (var processor in allProcessors.OrderByDescending(p => GetProcessorPriority(p.ProcessorType)))
        {
            if (processor.CanHandle(normalizedCommand))
            {
                _logger.LogDebug("Selected processor '{ProcessorType}' for command '{Command}'", 
                    processor.ProcessorType, command);
                return processor;
            }
        }

        _logger.LogDebug("No specific processor found for command '{Command}', using general processor", command);
        return GetProcessor("general");
    }

    public IEnumerable<ICommandProcessor> GetAllProcessors()
    {
        foreach (var processorType in _processors.Keys)
        {
            yield return GetProcessor(processorType);
        }
    }

    private int GetProcessorPriority(string processorType) => processorType.ToLowerInvariant() switch
    {
        "system" => 100,
        "help" => 90,
        "todo" => 80,
        "goal" => 70,
        "schedule" => 60,
        "general" => 10,
        _ => 50
    };
}