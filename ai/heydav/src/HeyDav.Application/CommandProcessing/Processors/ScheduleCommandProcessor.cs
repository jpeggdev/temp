using Microsoft.Extensions.Logging;

namespace HeyDav.Application.CommandProcessing.Processors;

public class ScheduleCommandProcessor : BaseCommandProcessor
{
    public ScheduleCommandProcessor(ILogger<ScheduleCommandProcessor> logger) : base(logger) { }

    public override string ProcessorType => "schedule";

    public override CommandCapabilities Capabilities => new()
    {
        SupportedCommands = new List<string>
        {
            "show schedule", "my schedule", "what's my schedule",
            "schedule for today", "schedule for tomorrow",
            "calendar", "appointments", "meetings"
        },
        SupportedSources = new List<string> { "voice", "email", "cli", "mobile" },
        RequiresContext = false,
        SupportsStreaming = false,
        Description = "Handles schedule and calendar-related commands",
        Examples = new List<string>
        {
            "show my schedule",
            "what's my schedule for today",
            "schedule for tomorrow"
        }
    };

    protected override async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        // Placeholder implementation - schedule feature coming soon
        var response = FormatResponseForSource(
            request.Source,
            "Schedule management is coming soon! For now, you can use tasks with due dates to track your timeline.",
            "📅 Schedule management is coming soon!\n\nFor now, you can use tasks with due dates to track your timeline. Try 'add task [description] due tomorrow'");

        var result = CreateSuccessResult(response, null, CommandAction.Query);
        result.SuggestedActions = new List<string>
        {
            "add task [description] due [date]",
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