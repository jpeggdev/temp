using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.CommandProcessing;
using HeyDav.Application.TodoManagement.Commands;
using HeyDav.Application.TodoManagement.Queries;
using HeyDav.Domain.TodoManagement.Enums;

namespace HeyDav.Cli.Services;

public class ReplService : IReplService
{
    private readonly ICommandOrchestrator _commandOrchestrator;
    private readonly IMediator _mediator;
    private bool _isRunning;
    private bool _verbose;

    public ReplService(ICommandOrchestrator commandOrchestrator, IMediator mediator)
    {
        _commandOrchestrator = commandOrchestrator;
        _mediator = mediator;
    }

    public async Task StartAsync(bool verbose = false)
    {
        _verbose = verbose;
        _isRunning = true;

        ShowWelcomeMessage();
        ShowHelp();

        while (_isRunning)
        {
            Console.Write("dav> ");
            var input = Console.ReadLine()?.Trim();

            if (string.IsNullOrEmpty(input))
                continue;

            await ExecuteCommandAsync(input);
        }
    }

    public async Task ExecuteCommandAsync(string command)
    {
        try
        {
            var parts = ParseCommand(command);
            if (parts.Length == 0) return;

            var cmd = parts[0].ToLowerInvariant();
            var args = parts.Skip(1).ToArray();

            // Handle system commands locally
            switch (cmd)
            {
                case "help":
                case "h":
                    ShowHelp();
                    return;

                case "exit":
                case "quit":
                case "q":
                    _isRunning = false;
                    Console.WriteLine("Goodbye!");
                    return;

                case "clear":
                case "cls":
                    Console.Clear();
                    ShowWelcomeMessage();
                    return;
            }

            // Try CommandOrchestrator first
            try
            {
                var result = await _commandOrchestrator.ProcessCommandAsync(command, "cli");
                
                if (result.Success)
                {
                    Console.WriteLine(result.Message);
                    if (_verbose)
                    {
                        Console.WriteLine($"Processed by: {result.ProcessorUsed} in {result.ProcessingTime.TotalMilliseconds:F2}ms");
                    }
                    return;
                }
                else
                {
                    // If CommandOrchestrator couldn't handle it, fall back to legacy handling
                    if (_verbose)
                    {
                        Console.WriteLine($"CommandOrchestrator couldn't handle: {result.Message}");
                    }
                }
            }
            catch (Exception ex)
            {
                if (_verbose)
                {
                    Console.WriteLine($"CommandOrchestrator error: {ex.Message}");
                }
            }

            // Fall back to legacy command handling
            switch (cmd)
            {
                case "todos":
                case "tasks":
                    await HandleTodosCommand(args);
                    break;

                case "todo":
                case "task":
                    await HandleTodoCommand(args);
                    break;

                case "goals":
                    await HandleGoalsCommand(args);
                    break;

                case "status":
                    await ShowStatus();
                    break;

                default:
                    Console.WriteLine($"Unknown command: {cmd}. Type 'help' for available commands.");
                    break;
            }
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error: {ex.Message}");
            if (_verbose)
            {
                Console.WriteLine($"Stack trace: {ex.StackTrace}");
            }
        }
    }

    private static string[] ParseCommand(string input)
    {
        var parts = new List<string>();
        var current = new System.Text.StringBuilder();
        var inQuotes = false;

        for (int i = 0; i < input.Length; i++)
        {
            var c = input[i];

            if (c == '"' && (i == 0 || input[i - 1] != '\\'))
            {
                inQuotes = !inQuotes;
            }
            else if (c == ' ' && !inQuotes)
            {
                if (current.Length > 0)
                {
                    parts.Add(current.ToString());
                    current.Clear();
                }
            }
            else
            {
                current.Append(c);
            }
        }

        if (current.Length > 0)
        {
            parts.Add(current.ToString());
        }

        return parts.ToArray();
    }

    private async Task HandleTodosCommand(string[] args)
    {
        if (args.Length == 0)
        {
            await ShowAllTodos();
            return;
        }

        var subCommand = args[0].ToLowerInvariant();
        var subArgs = args.Skip(1).ToArray();

        switch (subCommand)
        {
            case "list":
            case "show":
                await ShowAllTodos();
                break;

            case "add":
            case "create":
                await CreateTodo(subArgs);
                break;

            default:
                Console.WriteLine("Usage: todos [list|add] [arguments]");
                break;
        }
    }

    private async Task HandleTodoCommand(string[] args)
    {
        if (args.Length == 0)
        {
            Console.WriteLine("Usage: todo [add|complete|delete] [arguments]");
            return;
        }

        var subCommand = args[0].ToLowerInvariant();
        var subArgs = args.Skip(1).ToArray();

        switch (subCommand)
        {
            case "add":
            case "create":
                await CreateTodo(subArgs);
                break;

            case "complete":
            case "done":
                await CompleteTodo(subArgs);
                break;

            default:
                Console.WriteLine("Usage: todo [add|complete] [arguments]");
                break;
        }
    }

    private async Task HandleGoalsCommand(string[] args)
    {
        Console.WriteLine("Goals feature coming soon...");
        await Task.CompletedTask;
    }

    private async Task ShowAllTodos()
    {
        var todos = await _mediator.Send(new GetTodosQuery());
        
        if (!todos.Any())
        {
            Console.WriteLine("No todos found.");
            return;
        }

        Console.WriteLine("\n📋 Your Tasks:");
        Console.WriteLine(new string('-', 50));

        foreach (var todo in todos.OrderBy(t => t.Priority).ThenBy(t => t.CreatedAt))
        {
            var status = todo.Status == TodoStatus.Completed ? "✅" : "⭕";
            var priority = GetPrioritySymbol(todo.Priority);
            var dueDate = todo.DueDate?.ToString("MM/dd") ?? "";
            
            Console.WriteLine($"{status} {priority} {todo.Title} {dueDate}");
            
            if (_verbose && !string.IsNullOrEmpty(todo.Description))
            {
                Console.WriteLine($"    📝 {todo.Description}");
            }
        }
        Console.WriteLine();
    }

    private async Task CreateTodo(string[] args)
    {
        if (args.Length == 0)
        {
            Console.WriteLine("Usage: todo add \"Task title\" [--priority high|medium|low] [--due YYYY-MM-DD]");
            return;
        }

        var title = string.Join(" ", args.TakeWhile(arg => !arg.StartsWith("--")));
        var options = args.SkipWhile(arg => !arg.StartsWith("--")).ToArray();

        var priority = Priority.Medium;
        DateTime? dueDate = null;

        for (int i = 0; i < options.Length; i += 2)
        {
            if (i + 1 >= options.Length) break;

            var option = options[i];
            var value = options[i + 1];

            switch (option)
            {
                case "--priority":
                case "-p":
                    if (Enum.TryParse<Priority>(value, true, out var p))
                        priority = p;
                    break;

                case "--due":
                case "-d":
                    if (DateTime.TryParse(value, out var date))
                        dueDate = date;
                    break;
            }
        }

        var todoId = await _mediator.Send(new CreateTodoCommand(title, Priority: priority, DueDate: dueDate));
        Console.WriteLine($"✅ Created todo: {title} (ID: {todoId})");
    }

    private async Task CompleteTodo(string[] args)
    {
        Console.WriteLine("Complete todo feature coming soon...");
        await Task.CompletedTask;
    }

    private async Task ShowStatus()
    {
        var todos = await _mediator.Send(new GetTodosQuery());
        
        var completed = todos.Count(t => t.Status == TodoStatus.Completed);
        var pending = todos.Count(t => t.Status == TodoStatus.NotStarted);
        var inProgress = todos.Count(t => t.Status == TodoStatus.InProgress);

        Console.WriteLine("\n📊 Status Summary:");
        Console.WriteLine($"   ✅ Completed: {completed}");
        Console.WriteLine($"   🔄 In Progress: {inProgress}");
        Console.WriteLine($"   ⭕ Not Started: {pending}");
        Console.WriteLine($"   📋 Total: {todos.Count()}");
        Console.WriteLine();
    }

    private static string GetPrioritySymbol(Priority priority) => priority switch
    {
        Priority.High => "🔴",
        Priority.Medium => "🟡",
        Priority.Low => "🟢",
        _ => "⚪"
    };

    private static void ShowWelcomeMessage()
    {
        Console.WriteLine("╔══════════════════════════════════════╗");
        Console.WriteLine("║             Hey-Dav CLI              ║");
        Console.WriteLine("║    How Everything You Do Adds Value ║");
        Console.WriteLine("╚══════════════════════════════════════╝");
        Console.WriteLine();
    }

    private static void ShowHelp()
    {
        Console.WriteLine("Available commands:");
        Console.WriteLine("  help, h              - Show this help message");
        Console.WriteLine("  exit, quit, q        - Exit the application");
        Console.WriteLine("  clear, cls           - Clear the screen");
        Console.WriteLine("  status               - Show summary status");
        Console.WriteLine();
        Console.WriteLine("Todo Management:");
        Console.WriteLine("  todos                - List all todos");
        Console.WriteLine("  todos list           - List all todos");
        Console.WriteLine("  todos add \"title\"    - Add a new todo");
        Console.WriteLine("  todo add \"title\"     - Add a new todo");
        Console.WriteLine("  todo complete <id>   - Mark todo as complete");
        Console.WriteLine();
        Console.WriteLine("Options for 'add' commands:");
        Console.WriteLine("  --priority, -p       - Set priority (high|medium|low)");
        Console.WriteLine("  --due, -d            - Set due date (YYYY-MM-DD)");
        Console.WriteLine();
        Console.WriteLine("Goals:");
        Console.WriteLine("  goals                - Manage goals (coming soon)");
        Console.WriteLine();
    }
}