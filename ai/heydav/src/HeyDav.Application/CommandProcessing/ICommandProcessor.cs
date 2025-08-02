namespace HeyDav.Application.CommandProcessing;

public interface ICommandProcessor
{
    Task<CommandResult> ProcessAsync(CommandRequest request);
    bool CanHandle(string command);
    string ProcessorType { get; }
    CommandCapabilities Capabilities { get; }
}

public interface ICommandProcessorFactory
{
    ICommandProcessor GetProcessor(string processorType);
    ICommandProcessor GetBestProcessor(string command);
    IEnumerable<ICommandProcessor> GetAllProcessors();
}

public class CommandRequest
{
    public required string Command { get; set; }
    public required string Source { get; set; } // "voice", "email", "cli", "mobile"
    public Dictionary<string, object> Context { get; set; } = new();
    public string? ProcessorType { get; set; } // Specify which processor to use
    public CommandPriority Priority { get; set; } = CommandPriority.Normal;
    public DateTime Timestamp { get; set; } = DateTime.UtcNow;
    public string? UserId { get; set; }
    public Dictionary<string, string> Parameters { get; set; } = new();
}

public class CommandResult
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public object? Data { get; set; }
    public CommandAction Action { get; set; } = CommandAction.None;
    public List<string> SuggestedActions { get; set; } = new();
    public Dictionary<string, object> Metadata { get; set; } = new();
    public TimeSpan ProcessingTime { get; set; }
    public string ProcessorUsed { get; set; } = string.Empty;
}

public class CommandCapabilities
{
    public List<string> SupportedCommands { get; set; } = new();
    public List<string> SupportedSources { get; set; } = new();
    public bool RequiresContext { get; set; }
    public bool SupportsStreaming { get; set; }
    public Dictionary<string, string> Parameters { get; set; } = new();
    public string Description { get; set; } = string.Empty;
    public List<string> Examples { get; set; } = new();
}

public enum CommandPriority
{
    Low = 0,
    Normal = 1,
    High = 2,
    Critical = 3
}

public enum CommandAction
{
    None,
    Navigate,
    Create,
    Update,
    Delete,
    Execute,
    Query,
    Notify,
    Stream
}