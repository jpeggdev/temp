namespace HeyDav.Application.Email;

public interface IEmailCommandProcessor
{
    Task<EmailCommandResult> ProcessEmailAsync(EmailCommand emailCommand);
    bool CanProcessSubject(string subject);
    Task<string> GenerateEmailResponseAsync(EmailCommandResult result);
}

public class EmailCommand
{
    public required string From { get; set; }
    public required string Subject { get; set; }
    public required string Body { get; set; }
    public required DateTime ReceivedAt { get; set; }
    public string? MessageId { get; set; }
    public List<string> Recipients { get; set; } = new();
}

public class EmailCommandResult
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public object? Data { get; set; }
    public EmailAction Action { get; set; } = EmailAction.None;
    public List<string> SuggestedResponses { get; set; } = new();
}

public enum EmailAction
{
    None,
    TodoCreated,
    GoalCreated,
    StatusReport,
    TaskCompleted,
    ScheduleUpdate,
    QuickResponse
}