using Microsoft.Extensions.Logging;
using System.Diagnostics;
using System.Text.RegularExpressions;

namespace HeyDav.Application.CommandProcessing.Processors;

public abstract class BaseCommandProcessor : ICommandProcessor
{
    protected readonly ILogger Logger;
    
    protected BaseCommandProcessor(ILogger logger)
    {
        Logger = logger;
    }

    public abstract string ProcessorType { get; }
    public abstract CommandCapabilities Capabilities { get; }

    public virtual bool CanHandle(string command)
    {
        var normalizedCommand = command.ToLowerInvariant().Trim();
        return Capabilities.SupportedCommands.Any(cmd => 
            normalizedCommand.Contains(cmd.ToLowerInvariant()) ||
            IsCommandPatternMatch(normalizedCommand, cmd));
    }

    public async Task<CommandResult> ProcessAsync(CommandRequest request)
    {
        var stopwatch = Stopwatch.StartNew();
        
        try
        {
            Logger.LogInformation("Processing command '{Command}' with {ProcessorType} processor", 
                request.Command, ProcessorType);

            var result = await ProcessCommandAsync(request);
            result.ProcessorUsed = ProcessorType;
            result.ProcessingTime = stopwatch.Elapsed;

            Logger.LogInformation("Command processed successfully in {ElapsedMs}ms", 
                stopwatch.ElapsedMilliseconds);

            return result;
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Error processing command '{Command}' with {ProcessorType} processor", 
                request.Command, ProcessorType);

            return new CommandResult
            {
                Success = false,
                Message = $"Error processing command: {ex.Message}",
                ProcessorUsed = ProcessorType,
                ProcessingTime = stopwatch.Elapsed
            };
        }
    }

    protected abstract Task<CommandResult> ProcessCommandAsync(CommandRequest request);

    protected virtual bool IsCommandPatternMatch(string command, string pattern)
    {
        // Convert simple patterns to regex
        // Example: "add task *" becomes "add task .*"
        var regexPattern = pattern.Replace("*", ".*").Replace("?", ".?");
        try
        {
            return Regex.IsMatch(command, regexPattern, RegexOptions.IgnoreCase);
        }
        catch
        {
            return false;
        }
    }

    protected virtual Dictionary<string, string> ExtractParameters(string command, string pattern)
    {
        var parameters = new Dictionary<string, string>();
        
        // Simple parameter extraction logic
        // This could be enhanced to support more complex patterns
        if (pattern.Contains("*"))
        {
            var parts = pattern.Split('*');
            if (parts.Length == 2)
            {
                var prefix = parts[0].Trim();
                var commandLower = command.ToLowerInvariant();
                
                if (commandLower.StartsWith(prefix.ToLowerInvariant()))
                {
                    var value = command.Substring(prefix.Length).Trim();
                    if (!string.IsNullOrEmpty(value))
                    {
                        parameters["value"] = value;
                    }
                }
            }
        }

        return parameters;
    }

    protected virtual string GetParameterValue(CommandRequest request, string key, string defaultValue = "")
    {
        return request.Parameters.TryGetValue(key, out var value) ? value : defaultValue;
    }

    protected virtual bool HasParameter(CommandRequest request, string key)
    {
        return request.Parameters.ContainsKey(key) && !string.IsNullOrEmpty(request.Parameters[key]);
    }

    protected virtual CommandResult CreateSuccessResult(string message, object? data = null, CommandAction action = CommandAction.None)
    {
        return new CommandResult
        {
            Success = true,
            Message = message,
            Data = data,
            Action = action
        };
    }

    protected virtual CommandResult CreateErrorResult(string message, object? data = null)
    {
        return new CommandResult
        {
            Success = false,
            Message = message,
            Data = data
        };
    }
}