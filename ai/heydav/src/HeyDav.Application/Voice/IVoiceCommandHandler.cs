namespace HeyDav.Application.Voice;

public interface IVoiceCommandHandler
{
    Task<VoiceCommandResult> HandleCommandAsync(string command);
    bool CanHandle(string command);
}

public class VoiceCommandResult
{
    public bool Success { get; set; }
    public string Response { get; set; } = string.Empty;
    public object? Data { get; set; }
    public VoiceCommandAction? Action { get; set; }
}

public enum VoiceCommandAction
{
    None,
    NavigateToTodos,
    NavigateToGoals,
    NavigateToMood,
    NavigateToFinance,
    NavigateToNews,
    CreateNewTodo,
    ShowTodoList,
    MarkTodoComplete,
    LogMood,
    ShowSchedule
}