using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.TodoManagement.Commands;
using HeyDav.Application.TodoManagement.Queries;
using HeyDav.Domain.TodoManagement.Enums;
using Microsoft.Extensions.Logging;
using System.Text.RegularExpressions;

namespace HeyDav.Application.CommandProcessing.Processors;

public class TodoCommandProcessor : BaseCommandProcessor
{
    private readonly IMediator _mediator;

    public TodoCommandProcessor(IMediator mediator, ILogger<TodoCommandProcessor> logger) 
        : base(logger)
    {
        _mediator = mediator;
    }

    public override string ProcessorType => "todo";

    public override CommandCapabilities Capabilities => new()
    {
        SupportedCommands = new List<string>
        {
            "add task *", "create task *", "new task *",
            "add todo *", "create todo *", "new todo *",
            "show tasks", "list tasks", "my tasks",
            "show todos", "list todos", "my todos",
            "complete task *", "finish task *", "done task *",
            "delete task *", "remove task *",
            "task status", "todo status"
        },
        SupportedSources = new List<string> { "voice", "email", "cli", "mobile" },
        RequiresContext = false,
        SupportsStreaming = false,
        Description = "Handles task and todo management commands",
        Examples = new List<string>
        {
            "add task Buy groceries",
            "create todo Review project proposal",
            "show my tasks",
            "complete task 123",
            "list todos by priority"
        }
    };

    protected override async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        var command = request.Command.ToLowerInvariant().Trim();

        // Add task commands
        if (IsAddTaskCommand(command))
        {
            return await HandleAddTask(request);
        }

        // List/show task commands
        if (IsListTaskCommand(command))
        {
            return await HandleListTasks(request);
        }

        // Complete task commands
        if (IsCompleteTaskCommand(command))
        {
            return await HandleCompleteTask(request);
        }

        // Delete task commands
        if (IsDeleteTaskCommand(command))
        {
            return await HandleDeleteTask(request);
        }

        // Status commands
        if (IsStatusCommand(command))
        {
            return await HandleTaskStatus(request);
        }

        return CreateErrorResult("I didn't understand that task command. Try 'add task [description]' or 'show my tasks'.");
    }

    private bool IsAddTaskCommand(string command)
    {
        var patterns = new[]
        {
            @"^(add|create|new)\s+(task|todo):?\s*(.+)",
            @"^(task|todo)\s+(add|create|new):?\s*(.+)"
        };

        return patterns.Any(pattern => Regex.IsMatch(command, pattern, RegexOptions.IgnoreCase));
    }

    private bool IsListTaskCommand(string command)
    {
        var patterns = new[]
        {
            @"^(show|list|display)\s+(my\s+)?(tasks|todos)",
            @"^(tasks|todos)(\s+(list|show))?$",
            @"^my\s+(tasks|todos)$"
        };

        return patterns.Any(pattern => Regex.IsMatch(command, pattern, RegexOptions.IgnoreCase));
    }

    private bool IsCompleteTaskCommand(string command)
    {
        var patterns = new[]
        {
            @"^(complete|finish|done)\s+(task|todo)",
            @"^mark\s+(task|todo)\s+(complete|done)"
        };

        return patterns.Any(pattern => Regex.IsMatch(command, pattern, RegexOptions.IgnoreCase));
    }

    private bool IsDeleteTaskCommand(string command)
    {
        var patterns = new[]
        {
            @"^(delete|remove)\s+(task|todo)",
            @"^(task|todo)\s+(delete|remove)"
        };

        return patterns.Any(pattern => Regex.IsMatch(command, pattern, RegexOptions.IgnoreCase));
    }

    private bool IsStatusCommand(string command)
    {
        var patterns = new[]
        {
            @"^(task|todo)\s+status$",
            @"^status\s+(tasks|todos)$",
            @"^how\s+many\s+(tasks|todos)"
        };

        return patterns.Any(pattern => Regex.IsMatch(command, pattern, RegexOptions.IgnoreCase));
    }

    private async Task<CommandResult> HandleAddTask(CommandRequest request)
    {
        var taskContent = ExtractTaskContent(request.Command);
        
        if (string.IsNullOrWhiteSpace(taskContent))
        {
            return CreateErrorResult("Please specify what task you'd like to add. Example: 'add task Buy groceries'");
        }

        var priority = ExtractPriority(request.Command);
        var dueDate = ExtractDueDate(request.Command);

        try
        {
            var todoId = await _mediator.Send(new CreateTodoCommand(
                taskContent,
                Priority: priority,
                DueDate: dueDate));

            var response = FormatResponseForSource(
                request.Source,
                $"Task created: '{taskContent}'",
                $"✅ Created task: {taskContent}");

            return CreateSuccessResult(response, todoId, CommandAction.Create);
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to create task: {TaskContent}", taskContent);
            return CreateErrorResult("Sorry, I couldn't create that task. Please try again.");
        }
    }

    private async Task<CommandResult> HandleListTasks(CommandRequest request)
    {
        try
        {
            var todos = await _mediator.Send(new GetTodosQuery());
            
            if (!todos.Any())
            {
                var emptyResponse = FormatResponseForSource(
                    request.Source,
                    "No tasks found.",
                    "📋 You don't have any tasks yet. Create your first task!");

                return CreateSuccessResult(emptyResponse, todos, CommandAction.Query);
            }

            var response = FormatTaskListResponse(request.Source, todos);
            return CreateSuccessResult(response, todos, CommandAction.Query);
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to retrieve tasks");
            return CreateErrorResult("Sorry, I couldn't retrieve your tasks. Please try again.");
        }
    }

    private async Task<CommandResult> HandleCompleteTask(CommandRequest request)
    {
        // For now, return a placeholder since we need to implement task completion by ID
        return CreateErrorResult("Task completion by ID is not yet implemented. Use the desktop or mobile app to complete tasks.");
    }

    private async Task<CommandResult> HandleDeleteTask(CommandRequest request)
    {
        // For now, return a placeholder since we need to implement task deletion by ID
        return CreateErrorResult("Task deletion by ID is not yet implemented. Use the desktop or mobile app to delete tasks.");
    }

    private async Task<CommandResult> HandleTaskStatus(CommandRequest request)
    {
        try
        {
            var todos = await _mediator.Send(new GetTodosQuery());
            
            var completed = todos.Count(t => t.Status == TodoStatus.Completed);
            var inProgress = todos.Count(t => t.Status == TodoStatus.InProgress);
            var notStarted = todos.Count(t => t.Status == TodoStatus.NotStarted);
            var total = todos.Count();

            var response = FormatResponseForSource(
                request.Source,
                $"Task Status: {total} total, {completed} completed, {inProgress} in progress, {notStarted} not started",
                $"📊 Task Status:\n✅ Completed: {completed}\n🔄 In Progress: {inProgress}\n⭕ Not Started: {notStarted}\n📋 Total: {total}");

            return CreateSuccessResult(response, new { total, completed, inProgress, notStarted }, CommandAction.Query);
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to get task status");
            return CreateErrorResult("Sorry, I couldn't get your task status. Please try again.");
        }
    }

    private string ExtractTaskContent(string command)
    {
        var patterns = new[]
        {
            @"^(?:add|create|new)\s+(?:task|todo):?\s*(.+)",
            @"^(?:task|todo)\s+(?:add|create|new):?\s*(.+)"
        };

        foreach (var pattern in patterns)
        {
            var match = Regex.Match(command, pattern, RegexOptions.IgnoreCase);
            if (match.Success && match.Groups.Count > 1)
            {
                return match.Groups[1].Value.Trim();
            }
        }

        return string.Empty;
    }

    private Priority ExtractPriority(string command)
    {
        var lowerCommand = command.ToLowerInvariant();
        
        if (lowerCommand.Contains("high priority") || lowerCommand.Contains("urgent") || lowerCommand.Contains("important"))
            return Priority.High;
        if (lowerCommand.Contains("low priority") || lowerCommand.Contains("when you have time"))
            return Priority.Low;
        
        return Priority.Medium;
    }

    private DateTime? ExtractDueDate(string command)
    {
        var patterns = new[]
        {
            @"due:?\s*(\d{4}-\d{2}-\d{2})",
            @"by:?\s*(\d{4}-\d{2}-\d{2})",
            @"deadline:?\s*(\d{4}-\d{2}-\d{2})",
            @"tomorrow",
            @"today",
            @"next week"
        };

        foreach (var pattern in patterns)
        {
            var match = Regex.Match(command, pattern, RegexOptions.IgnoreCase);
            if (match.Success)
            {
                if (DateTime.TryParse(match.Groups[1].Value, out var date))
                    return date;
                
                // Handle relative dates
                if (match.Value.ToLowerInvariant().Contains("tomorrow"))
                    return DateTime.Today.AddDays(1);
                if (match.Value.ToLowerInvariant().Contains("today"))
                    return DateTime.Today;
                if (match.Value.ToLowerInvariant().Contains("next week"))
                    return DateTime.Today.AddDays(7);
            }
        }

        return null;
    }

    private string FormatResponseForSource(string source, string textResponse, string richResponse)
    {
        return source.ToLowerInvariant() switch
        {
            "cli" or "email" => textResponse,
            _ => richResponse
        };
    }

    private string FormatTaskListResponse(string source, IEnumerable<dynamic> todos)
    {
        var taskList = todos.ToList();
        
        if (source.ToLowerInvariant() == "cli" || source.ToLowerInvariant() == "email")
        {
            var response = $"You have {taskList.Count} tasks:\n";
            foreach (var todo in taskList.Take(10))
            {
                var status = todo.Status == TodoStatus.Completed ? "[✓]" : "[ ]";
                var priority = GetPrioritySymbol(todo.Priority);
                response += $"{status} {priority} {todo.Title}\n";
            }
            return response.TrimEnd();
        }
        else
        {
            return $"📋 You have {taskList.Count} tasks. Check the Tasks screen for details.";
        }
    }

    private string GetPrioritySymbol(Priority priority) => priority switch
    {
        Priority.High => "🔴",
        Priority.Medium => "🟡",
        Priority.Low => "🟢",
        _ => "⚪"
    };
}