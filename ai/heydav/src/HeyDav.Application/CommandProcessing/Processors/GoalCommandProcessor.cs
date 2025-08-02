using Microsoft.Extensions.Logging;

namespace HeyDav.Application.CommandProcessing.Processors;

public class GoalCommandProcessor : BaseCommandProcessor
{
    public GoalCommandProcessor(ILogger<GoalCommandProcessor> logger) : base(logger) { }

    public override string ProcessorType => "goal";

    public override CommandCapabilities Capabilities => new()
    {
        SupportedCommands = new List<string>
        {
            "add goal *", "create goal *", "new goal *",
            "show goals", "list goals", "my goals",
            "goal status", "goal progress"
        },
        SupportedSources = new List<string> { "voice", "email", "cli", "mobile" },
        RequiresContext = false,
        SupportsStreaming = false,
        Description = "Handles goal management and tracking commands",
        Examples = new List<string>
        {
            "add goal Lose 10 pounds by summer",
            "create goal Learn Spanish in 6 months",
            "show my goals",
            "goal progress"
        }
    };

    protected override async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        // Placeholder implementation - goals feature coming soon
        var response = FormatResponseForSource(
            request.Source,
            "Goal management is coming soon! For now, you can use tasks to track your objectives.",
            "🎯 Goal management is coming soon!\n\nFor now, you can use tasks to track your objectives. Try 'add task [your goal as a task]'");

        var result = CreateSuccessResult(response, null, CommandAction.Query);
        result.SuggestedActions = new List<string>
        {
            "add task [your goal as a task]",
            "show my tasks"
        };
        return result;
    }

    private string FormatResponseForSource(string source, string textResponse, string richResponse)
    {
        return source.ToLowerInvariant() switch
        {
            "cli" or "email" => textResponse,
            _ => richResponse
        };
    }
}