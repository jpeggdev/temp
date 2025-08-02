using Microsoft.Extensions.Logging;

namespace HeyDav.Application.CommandProcessing.Processors;

public class GeneralCommandProcessor : BaseCommandProcessor
{
    public GeneralCommandProcessor(ILogger<GeneralCommandProcessor> logger) : base(logger) { }

    public override string ProcessorType => "general";

    public override CommandCapabilities Capabilities => new()
    {
        SupportedCommands = new List<string> { "*" }, // Handles everything as fallback
        SupportedSources = new List<string> { "voice", "email", "cli", "mobile" },
        RequiresContext = false,
        SupportsStreaming = false,
        Description = "General purpose command processor for unrecognized commands",
        Examples = new List<string>
        {
            "Any unrecognized command will be handled here"
        }
    };

    protected override async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        var command = request.Command.ToLowerInvariant().Trim();

        // Try to provide helpful suggestions based on command content
        var suggestions = GenerateSuggestions(command);

        var response = $"I'm not sure how to handle '{request.Command}'. ";
        
        if (suggestions.Any())
        {
            response += "Did you mean one of these?\n" + string.Join("\n", suggestions.Select(s => $"• {s}"));
        }
        else
        {
            response += "Try saying 'help' to see available commands.";
        }

        var result = CreateErrorResult(response);
        result.SuggestedActions = suggestions;
        return result;
    }

    private List<string> GenerateSuggestions(string command)
    {
        var suggestions = new List<string>();

        // Task-related suggestions
        if (ContainsAny(command, new[] { "task", "todo", "do", "add", "create", "make" }))
        {
            suggestions.Add("add task [description]");
            suggestions.Add("show my tasks");
        }

        // Status-related suggestions
        if (ContainsAny(command, new[] { "status", "summary", "report", "how many" }))
        {
            suggestions.Add("status");
            suggestions.Add("show my tasks");
        }

        // Help-related suggestions
        if (ContainsAny(command, new[] { "help", "what", "how", "commands" }))
        {
            suggestions.Add("help");
        }

        // Goal-related suggestions
        if (ContainsAny(command, new[] { "goal", "target", "objective", "plan" }))
        {
            suggestions.Add("goals (coming soon)");
        }

        return suggestions;
    }

    private bool ContainsAny(string text, string[] keywords)
    {
        return keywords.Any(keyword => text.Contains(keyword));
    }
}