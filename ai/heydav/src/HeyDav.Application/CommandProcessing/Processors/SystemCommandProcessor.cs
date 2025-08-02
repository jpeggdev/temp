using Microsoft.Extensions.Logging;

namespace HeyDav.Application.CommandProcessing.Processors;

public class SystemCommandProcessor : BaseCommandProcessor
{
    public SystemCommandProcessor(ILogger<SystemCommandProcessor> logger) : base(logger) { }

    public override string ProcessorType => "system";

    public override CommandCapabilities Capabilities => new()
    {
        SupportedCommands = new List<string>
        {
            "status", "health", "ping", "version",
            "clear", "cls", "exit", "quit",
            "settings", "config", "preferences"
        },
        SupportedSources = new List<string> { "voice", "email", "cli", "mobile" },
        RequiresContext = false,
        SupportsStreaming = false,
        Description = "Handles system-level commands and status inquiries",
        Examples = new List<string>
        {
            "status",
            "health check",
            "version",
            "clear screen"
        }
    };

    protected override async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        var command = request.Command.ToLowerInvariant().Trim();

        if (IsStatusCommand(command))
        {
            return await HandleStatusCommand(request);
        }

        if (IsClearCommand(command))
        {
            return HandleClearCommand(request);
        }

        if (IsVersionCommand(command))
        {
            return HandleVersionCommand(request);
        }

        if (IsExitCommand(command))
        {
            return HandleExitCommand(request);
        }

        return CreateErrorResult("Unknown system command. Try 'status', 'version', or 'clear'.");
    }

    private bool IsStatusCommand(string command)
    {
        return command.Contains("status") || command.Contains("health") || command.Contains("ping");
    }

    private bool IsClearCommand(string command)
    {
        return command == "clear" || command == "cls" || command.Contains("clear screen");
    }

    private bool IsVersionCommand(string command)
    {
        return command.Contains("version") || command.Contains("about");
    }

    private bool IsExitCommand(string command)
    {
        return command == "exit" || command == "quit" || command == "bye";
    }

    private async Task<CommandResult> HandleStatusCommand(CommandRequest request)
    {
        var uptime = Environment.TickCount64 / 1000; // seconds
        var memoryUsage = GC.GetTotalMemory(false) / 1024 / 1024; // MB

        var response = FormatResponseForSource(
            request.Source,
            $"Hey-Dav is running. Uptime: {uptime}s, Memory: {memoryUsage}MB",
            $"🟢 Hey-Dav Status: Online\n⏱️ Uptime: {uptime} seconds\n💾 Memory: {memoryUsage} MB\n🤖 All systems operational");

        return CreateSuccessResult(response, new { 
            status = "online",
            uptime = uptime,
            memoryUsageMB = memoryUsage,
            timestamp = DateTime.UtcNow
        }, CommandAction.Query);
    }

    private CommandResult HandleClearCommand(CommandRequest request)
    {
        if (request.Source.ToLowerInvariant() == "cli")
        {
            var result = CreateSuccessResult("Screen cleared", null, CommandAction.Execute);
            result.Metadata = new Dictionary<string, object> { ["action"] = "clear_screen" };
            return result;
        }

        return CreateSuccessResult("Clear command received", null, CommandAction.Execute);
    }

    private CommandResult HandleVersionCommand(CommandRequest request)
    {
        var version = "1.0.0"; // This would typically come from assembly info
        var buildDate = DateTime.Now.ToString("yyyy-MM-dd"); // This would come from build info

        var response = FormatResponseForSource(
            request.Source,
            $"Hey-Dav version {version} (built {buildDate})",
            $"🤖 Hey-Dav Assistant\n📱 Version: {version}\n🔧 Built: {buildDate}\n🎯 How Everything You Do Adds Value");

        return CreateSuccessResult(response, new { 
            version = version,
            buildDate = buildDate,
            name = "Hey-Dav"
        }, CommandAction.Query);
    }

    private CommandResult HandleExitCommand(CommandRequest request)
    {
        var response = FormatResponseForSource(
            request.Source,
            "Goodbye! Thanks for using Hey-Dav.",
            "👋 Goodbye! Thanks for using Hey-Dav.\n\nHave a productive day!");

        var result = CreateSuccessResult(response, null, CommandAction.Execute);
        result.Metadata = new Dictionary<string, object> { ["action"] = "exit" };
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