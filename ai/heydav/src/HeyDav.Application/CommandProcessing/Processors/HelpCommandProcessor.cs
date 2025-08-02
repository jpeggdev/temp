using Microsoft.Extensions.Logging;

namespace HeyDav.Application.CommandProcessing.Processors;

public class HelpCommandProcessor : BaseCommandProcessor
{
    private readonly ICommandProcessorFactory _processorFactory;

    public HelpCommandProcessor(ICommandProcessorFactory processorFactory, ILogger<HelpCommandProcessor> logger) 
        : base(logger)
    {
        _processorFactory = processorFactory;
    }

    public override string ProcessorType => "help";

    public override CommandCapabilities Capabilities => new()
    {
        SupportedCommands = new List<string>
        {
            "help", "?", "commands", "what can you do", "how to use", "usage"
        },
        SupportedSources = new List<string> { "voice", "email", "cli", "mobile" },
        RequiresContext = false,
        SupportsStreaming = false,
        Description = "Provides help and information about available commands",
        Examples = new List<string>
        {
            "help",
            "what can you do",
            "show me the commands"
        }
    };

    protected override async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        var command = request.Command.ToLowerInvariant().Trim();

        if (IsGeneralHelpCommand(command))
        {
            return await HandleGeneralHelp(request);
        }

        if (IsSpecificHelpCommand(command))
        {
            return await HandleSpecificHelp(request);
        }

        return await HandleGeneralHelp(request);
    }

    private bool IsGeneralHelpCommand(string command)
    {
        var patterns = new[]
        {
            "help", "commands", "what can you do", "usage", "how to use"
        };

        return patterns.Any(pattern => command.Contains(pattern));
    }

    private bool IsSpecificHelpCommand(string command)
    {
        return command.Contains("help") && (
            command.Contains("task") ||
            command.Contains("todo") ||
            command.Contains("goal") ||
            command.Contains("schedule"));
    }

    private async Task<CommandResult> HandleGeneralHelp(CommandRequest request)
    {
        var helpText = GenerateHelpText(request.Source);
        
        var result = CreateSuccessResult(helpText, null, CommandAction.Query);
        result.Metadata = new Dictionary<string, object>
        {
            ["helpType"] = "general",
            ["availableProcessors"] = _processorFactory.GetAllProcessors().Select(p => p.ProcessorType).ToList()
        };
        return result;
    }

    private async Task<CommandResult> HandleSpecificHelp(CommandRequest request)
    {
        var command = request.Command.ToLowerInvariant();
        string processorType = "";

        if (command.Contains("task") || command.Contains("todo"))
            processorType = "todo";
        else if (command.Contains("goal"))
            processorType = "goal";
        else if (command.Contains("schedule"))
            processorType = "schedule";

        if (!string.IsNullOrEmpty(processorType))
        {
            var processor = _processorFactory.GetProcessor(processorType);
            var specificHelp = GenerateSpecificHelp(processor, request.Source);
            
            var result = CreateSuccessResult(specificHelp, null, CommandAction.Query);
            result.Metadata = new Dictionary<string, object>
            {
                ["helpType"] = "specific",
                ["processorType"] = processorType
            };
            return result;
        }

        return await HandleGeneralHelp(request);
    }

    private string GenerateHelpText(string source)
    {
        var isRichFormat = source.ToLowerInvariant() != "cli" && source.ToLowerInvariant() != "email";

        if (isRichFormat)
        {
            return @"🤖 Hey-Dav Assistant Help

I can help you with:

📋 Tasks & Todos:
• ""add task [description]"" - Create a new task
• ""show my tasks"" - List all your tasks
• ""task status"" - Get task summary

🎯 Goals (Coming Soon):
• ""add goal [description]"" - Create a new goal
• ""show my goals"" - List your goals

📊 Status & Reports:
• ""status"" - Get overall status
• ""summary"" - Get daily summary

💬 General:
• ""help"" - Show this help message
• ""help tasks"" - Get task-specific help

Try saying any of these commands naturally!";
        }
        else
        {
            return @"Hey-Dav Assistant Commands:

TASKS & TODOS:
- add task [description]     Create a new task
- show my tasks             List all your tasks  
- task status               Get task summary

GOALS (Coming Soon):
- add goal [description]    Create a new goal
- show my goals            List your goals

STATUS & REPORTS:
- status                   Get overall status
- summary                  Get daily summary

GENERAL:
- help                     Show this help
- help tasks               Get task-specific help

You can use these commands via voice, email, CLI, or mobile app.";
        }
    }

    private string GenerateSpecificHelp(ICommandProcessor processor, string source)
    {
        var capabilities = processor.Capabilities;
        var isRichFormat = source.ToLowerInvariant() != "cli" && source.ToLowerInvariant() != "email";

        var help = $"{(isRichFormat ? "🔧" : "")} {processor.ProcessorType.ToUpper()} Commands:\n\n";
        help += $"{capabilities.Description}\n\n";

        if (capabilities.Examples.Any())
        {
            help += "Examples:\n";
            foreach (var example in capabilities.Examples)
            {
                help += $"{(isRichFormat ? "•" : "-")} {example}\n";
            }
        }

        return help;
    }
}