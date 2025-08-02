using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.TodoManagement.Commands;
using HeyDav.Application.TodoManagement.Queries;

namespace HeyDav.Application.Voice;

public class VoiceCommandProcessor : IVoiceCommandHandler
{
    private readonly IMediator _mediator;
    private readonly Dictionary<string, Func<string, Task<VoiceCommandResult>>> _commandHandlers;

    public VoiceCommandProcessor(IMediator mediator)
    {
        _mediator = mediator;
        _commandHandlers = InitializeCommandHandlers();
    }

    private Dictionary<string, Func<string, Task<VoiceCommandResult>>> InitializeCommandHandlers()
    {
        return new Dictionary<string, Func<string, Task<VoiceCommandResult>>>(StringComparer.OrdinalIgnoreCase)
        {
            // Todo commands
            { "show tasks", HandleShowTasks },
            { "show my tasks", HandleShowTasks },
            { "what are my tasks", HandleShowTasks },
            { "show todos", HandleShowTasks },
            { "show my todos", HandleShowTasks },
            
            { "add task", HandleAddTask },
            { "add todo", HandleAddTask },
            { "create task", HandleAddTask },
            { "create todo", HandleAddTask },
            { "new task", HandleAddTask },
            { "new todo", HandleAddTask },
            
            // Navigation commands
            { "go to tasks", HandleNavigateToTodos },
            { "open tasks", HandleNavigateToTodos },
            { "navigate to tasks", HandleNavigateToTodos },
            
            { "go to goals", HandleNavigateToGoals },
            { "open goals", HandleNavigateToGoals },
            { "show goals", HandleNavigateToGoals },
            
            { "check mood", HandleNavigateToMood },
            { "log mood", HandleLogMood },
            { "how am i feeling", HandleNavigateToMood },
            
            { "show schedule", HandleShowSchedule },
            { "what's my schedule", HandleShowSchedule },
            { "what's on my calendar", HandleShowSchedule },
            
            // General commands
            { "help", HandleHelp },
            { "what can you do", HandleHelp },
            { "commands", HandleHelp }
        };
    }

    public async Task<VoiceCommandResult> HandleCommandAsync(string command)
    {
        if (string.IsNullOrWhiteSpace(command))
        {
            return new VoiceCommandResult
            {
                Success = false,
                Response = "I didn't catch that. Please try again."
            };
        }

        var normalizedCommand = command.Trim().ToLowerInvariant();
        
        // Try exact match first
        if (_commandHandlers.TryGetValue(normalizedCommand, out var handler))
        {
            return await handler(command);
        }

        // Try partial matches
        foreach (var kvp in _commandHandlers)
        {
            if (normalizedCommand.Contains(kvp.Key))
            {
                return await kvp.Value(command);
            }
        }

        // Handle dynamic commands (e.g., "add task buy milk")
        if (normalizedCommand.StartsWith("add task ") || normalizedCommand.StartsWith("add todo ") ||
            normalizedCommand.StartsWith("create task ") || normalizedCommand.StartsWith("create todo "))
        {
            return await HandleAddTaskWithContent(command);
        }

        return new VoiceCommandResult
        {
            Success = false,
            Response = "I'm not sure what you want me to do. Try saying 'help' to see available commands."
        };
    }

    public bool CanHandle(string command)
    {
        if (string.IsNullOrWhiteSpace(command)) return false;
        
        var normalizedCommand = command.Trim().ToLowerInvariant();
        
        // Check exact matches
        if (_commandHandlers.ContainsKey(normalizedCommand)) return true;
        
        // Check partial matches
        if (_commandHandlers.Keys.Any(key => normalizedCommand.Contains(key))) return true;
        
        // Check dynamic commands
        return normalizedCommand.StartsWith("add task ") || 
               normalizedCommand.StartsWith("add todo ") ||
               normalizedCommand.StartsWith("create task ") || 
               normalizedCommand.StartsWith("create todo ");
    }

    private async Task<VoiceCommandResult> HandleShowTasks(string command)
    {
        var todos = await _mediator.Send(new GetTodosQuery());
        
        return new VoiceCommandResult
        {
            Success = true,
            Response = $"You have {todos.Count()} tasks.",
            Action = VoiceCommandAction.ShowTodoList,
            Data = todos
        };
    }

    private Task<VoiceCommandResult> HandleAddTask(string command)
    {
        return Task.FromResult(new VoiceCommandResult
        {
            Success = true,
            Response = "What task would you like to add?",
            Action = VoiceCommandAction.CreateNewTodo
        });
    }

    private async Task<VoiceCommandResult> HandleAddTaskWithContent(string command)
    {
        // Extract task content from command
        var prefixes = new[] { "add task ", "add todo ", "create task ", "create todo " };
        var taskContent = command;
        
        foreach (var prefix in prefixes)
        {
            if (command.ToLowerInvariant().StartsWith(prefix))
            {
                taskContent = command.Substring(prefix.Length).Trim();
                break;
            }
        }

        if (string.IsNullOrWhiteSpace(taskContent))
        {
            return new VoiceCommandResult
            {
                Success = false,
                Response = "Please specify what task you'd like to add."
            };
        }

        try
        {
            var todoId = await _mediator.Send(new CreateTodoCommand(
                Title: taskContent,
                Priority: Domain.TodoManagement.Enums.Priority.Medium));

            return new VoiceCommandResult
            {
                Success = true,
                Response = $"I've added '{taskContent}' to your tasks.",
                Action = VoiceCommandAction.CreateNewTodo,
                Data = todoId
            };
        }
        catch (Exception)
        {
            return new VoiceCommandResult
            {
                Success = false,
                Response = "Sorry, I couldn't add that task. Please try again."
            };
        }
    }

    private Task<VoiceCommandResult> HandleNavigateToTodos(string command)
    {
        return Task.FromResult(new VoiceCommandResult
        {
            Success = true,
            Response = "Opening your tasks.",
            Action = VoiceCommandAction.NavigateToTodos
        });
    }

    private Task<VoiceCommandResult> HandleNavigateToGoals(string command)
    {
        return Task.FromResult(new VoiceCommandResult
        {
            Success = true,
            Response = "Opening your goals.",
            Action = VoiceCommandAction.NavigateToGoals
        });
    }

    private Task<VoiceCommandResult> HandleNavigateToMood(string command)
    {
        return Task.FromResult(new VoiceCommandResult
        {
            Success = true,
            Response = "Opening mood tracker.",
            Action = VoiceCommandAction.NavigateToMood
        });
    }

    private Task<VoiceCommandResult> HandleLogMood(string command)
    {
        return Task.FromResult(new VoiceCommandResult
        {
            Success = true,
            Response = "How are you feeling?",
            Action = VoiceCommandAction.LogMood
        });
    }

    private Task<VoiceCommandResult> HandleShowSchedule(string command)
    {
        return Task.FromResult(new VoiceCommandResult
        {
            Success = true,
            Response = "Here's your schedule.",
            Action = VoiceCommandAction.ShowSchedule
        });
    }

    private Task<VoiceCommandResult> HandleHelp(string command)
    {
        var helpText = @"You can say things like:
- 'Show my tasks' to see your todo list
- 'Add task' followed by what you want to do
- 'Go to goals' to open your goals
- 'Check mood' to track how you're feeling
- 'Show schedule' to see your calendar";

        return Task.FromResult(new VoiceCommandResult
        {
            Success = true,
            Response = helpText
        });
    }
}