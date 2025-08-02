using HeyDav.Application.Services;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.CommandProcessing.Processors;

public class ExternalAiCommandProcessor : BaseCommandProcessor
{
    private readonly IExternalAiService _externalAiService;

    public ExternalAiCommandProcessor(IExternalAiService externalAiService, ILogger<ExternalAiCommandProcessor> logger) : base(logger)
    {
        _externalAiService = externalAiService;
    }

    public override string ProcessorType => "external-ai";

    public override CommandCapabilities Capabilities => new()
    {
        Description = "Routes commands to external AI CLI tools like Claude Code CLI and Gemini CLI",
        SupportedCommands = new List<string>
        {
            "claude",
            "gemini", 
            "openai",
            "ai",
            "code-review",
            "explain-code",
            "refactor",
            "generate-code"
        },
        Examples = new List<string>
        {
            "claude explain this code",
            "gemini what is machine learning",
            "ai help me write a function",
            "code-review check this file"
        }
    };

    public override bool CanHandle(string command)
    {
        var normalizedCommand = command.ToLowerInvariant().Trim();
        
        return normalizedCommand.StartsWith("claude ") ||
               normalizedCommand.StartsWith("gemini ") ||
               normalizedCommand.StartsWith("openai ") ||
               normalizedCommand.StartsWith("ai ") ||
               normalizedCommand.StartsWith("code-review") ||
               normalizedCommand.StartsWith("explain-code") ||
               normalizedCommand.StartsWith("refactor") ||
               normalizedCommand.StartsWith("generate-code");
    }

    protected override async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        try
        {
            Logger?.LogInformation("Processing external AI command: {Command}", request.Command);

            var (provider, actualCommand) = ParseCommand(request.Command);
            
            if (provider == null)
            {
                return new CommandResult
                {
                    Success = false,
                    Message = "Could not determine AI provider from command. Try prefixing with 'claude', 'gemini', or 'openai'.",
                    ProcessorUsed = ProcessorType
                };
            }

            // Check if provider is available
            if (!await _externalAiService.IsProviderAvailableAsync(provider.Value))
            {
                return new CommandResult
                {
                    Success = false,
                    Message = $"External AI provider '{provider}' is not available. Please ensure the CLI tool is installed and configured.",
                    ProcessorUsed = ProcessorType
                };
            }

            // Execute the command
            var context = new Dictionary<string, object>(request.Context)
            {
                { "original_command", request.Command },
                { "processor", ProcessorType }
            };

            string response = provider.Value switch
            {
                ExternalAiProvider.Claude => await _externalAiService.ProcessWithClaudeAsync(actualCommand, string.Join(", ", context.Values)),
                ExternalAiProvider.Gemini => await _externalAiService.ProcessWithGeminiAsync(actualCommand, string.Join(", ", context.Values)),
                ExternalAiProvider.OpenAI => await _externalAiService.ProcessWithOpenAiAsync(actualCommand, string.Join(", ", context.Values)),
                _ => throw new NotSupportedException($"Provider {provider.Value} is not supported")
            };

            return new CommandResult
            {
                Success = true,
                Message = response,
                ProcessorUsed = ProcessorType,
                Data = new { provider = provider.Value.ToString(), command = actualCommand }
            };
        }
        catch (Exception ex)
        {
            Logger?.LogError(ex, "Error processing external AI command: {Command}", request.Command);
            return new CommandResult
            {
                Success = false,
                Message = "An error occurred while processing the external AI command.",
                ProcessorUsed = ProcessorType
            };
        }
    }

    private (ExternalAiProvider?, string) ParseCommand(string command)
    {
        var normalizedCommand = command.ToLowerInvariant().Trim();

        if (normalizedCommand.StartsWith("claude "))
        {
            return (ExternalAiProvider.Claude, command.Substring(7));
        }
        
        if (normalizedCommand.StartsWith("gemini "))
        {
            return (ExternalAiProvider.Gemini, command.Substring(7));
        }
        
        if (normalizedCommand.StartsWith("openai "))
        {
            return (ExternalAiProvider.OpenAI, command.Substring(7));
        }

        if (normalizedCommand.StartsWith("ai "))
        {
            // Default to Claude for generic AI commands
            return (ExternalAiProvider.Claude, command.Substring(3));
        }

        // Handle specific command mappings
        if (normalizedCommand.StartsWith("code-review"))
        {
            return (ExternalAiProvider.Claude, command);
        }

        if (normalizedCommand.StartsWith("explain-code"))
        {
            return (ExternalAiProvider.Claude, command);
        }

        if (normalizedCommand.StartsWith("refactor"))
        {
            return (ExternalAiProvider.Claude, command);
        }

        if (normalizedCommand.StartsWith("generate-code"))
        {
            return (ExternalAiProvider.Claude, command);
        }

        return (null, command);
    }
}