using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.CommandProcessing;
using HeyDav.Application.TodoManagement.Commands;
using HeyDav.Application.TodoManagement.Queries;
using HeyDav.Domain.TodoManagement.Enums;
using System.Text.RegularExpressions;

namespace HeyDav.Application.Email;

public class EmailCommandProcessor : IEmailCommandProcessor
{
    private readonly ICommandOrchestrator _commandOrchestrator;
    private readonly IMediator _mediator;
    private readonly Dictionary<string, Func<EmailCommand, Task<EmailCommandResult>>> _subjectHandlers;
    private readonly Dictionary<Regex, Func<EmailCommand, Task<EmailCommandResult>>> _patternHandlers;

    public EmailCommandProcessor(ICommandOrchestrator commandOrchestrator, IMediator mediator)
    {
        _commandOrchestrator = commandOrchestrator;
        _mediator = mediator;
        _subjectHandlers = InitializeSubjectHandlers();
        _patternHandlers = InitializePatternHandlers();
    }

    private Dictionary<string, Func<EmailCommand, Task<EmailCommandResult>>> InitializeSubjectHandlers()
    {
        return new Dictionary<string, Func<EmailCommand, Task<EmailCommandResult>>>(StringComparer.OrdinalIgnoreCase)
        {
            { "Todo", HandleTodoEmail },
            { "Task", HandleTodoEmail },
            { "Add Task", HandleTodoEmail },
            { "New Todo", HandleTodoEmail },
            { "Status", HandleStatusRequest },
            { "Report", HandleStatusRequest },
            { "Quick Status", HandleStatusRequest },
            { "My Tasks", HandleStatusRequest },
            { "Help", HandleHelpRequest },
            { "Commands", HandleHelpRequest }
        };
    }

    private Dictionary<Regex, Func<EmailCommand, Task<EmailCommandResult>>> InitializePatternHandlers()
    {
        return new Dictionary<Regex, Func<EmailCommand, Task<EmailCommandResult>>>
        {
            { new Regex(@"^(add|create|new)\s+(task|todo):?\s*(.+)", RegexOptions.IgnoreCase), HandleTodoFromPattern },
            { new Regex(@"^reminder:?\s*(.+)", RegexOptions.IgnoreCase), HandleReminderEmail },
            { new Regex(@"^(complete|done|finish):?\s*(.+)", RegexOptions.IgnoreCase), HandleCompleteTaskEmail },
            { new Regex(@"^(status|report)\s+(for|of)?\s*(.+)", RegexOptions.IgnoreCase), HandleStatusRequest }
        };
    }

    public async Task<EmailCommandResult> ProcessEmailAsync(EmailCommand emailCommand)
    {
        try
        {
            // First try CommandOrchestrator with the email content
            var commandText = !string.IsNullOrWhiteSpace(emailCommand.Subject) 
                ? emailCommand.Subject 
                : emailCommand.Body;

            try
            {
                var context = new Dictionary<string, object>
                {
                    { "source_type", "email" },
                    { "from", emailCommand.From },
                    { "subject", emailCommand.Subject },
                    { "body", emailCommand.Body },
                    { "timestamp", emailCommand.ReceivedAt }
                };

                var result = await _commandOrchestrator.ProcessCommandAsync(commandText, "email", context);
                
                if (result.Success)
                {
                    return new EmailCommandResult
                    {
                        Success = true,
                        Message = result.Message,
                        Action = MapToEmailAction(result),
                        Data = result.Data
                    };
                }
            }
            catch (Exception)
            {
                // Fall back to legacy email processing
            }

            // Fall back to legacy email processing
            // First try exact subject matches
            if (_subjectHandlers.TryGetValue(emailCommand.Subject.Trim(), out var handler))
            {
                return await handler(emailCommand);
            }

            // Then try pattern matching on subject
            foreach (var kvp in _patternHandlers)
            {
                if (kvp.Key.IsMatch(emailCommand.Subject))
                {
                    return await kvp.Value(emailCommand);
                }
            }

            // If no subject match, try parsing the body
            return await ParseEmailBody(emailCommand);
        }
        catch (Exception ex)
        {
            return new EmailCommandResult
            {
                Success = false,
                Message = $"Error processing email: {ex.Message}",
                Action = EmailAction.QuickResponse,
                SuggestedResponses = { "I encountered an error processing your request. Please try again or contact support." }
            };
        }
    }

    public bool CanProcessSubject(string subject)
    {
        if (_subjectHandlers.ContainsKey(subject.Trim()))
            return true;

        return _patternHandlers.Keys.Any(pattern => pattern.IsMatch(subject));
    }

    public async Task<string> GenerateEmailResponseAsync(EmailCommandResult result)
    {
        var response = $"Hello,\n\n{result.Message}\n\n";

        if (result.Action != EmailAction.None)
        {
            response += GetActionDescription(result.Action) + "\n\n";
        }

        if (result.SuggestedResponses.Any())
        {
            response += "Quick actions you can try:\n";
            foreach (var suggestion in result.SuggestedResponses)
            {
                response += $"• {suggestion}\n";
            }
            response += "\n";
        }

        response += "Best regards,\nHey-Dav Assistant";

        return await Task.FromResult(response);
    }

    private async Task<EmailCommandResult> HandleTodoEmail(EmailCommand emailCommand)
    {
        var taskContent = ExtractTaskContent(emailCommand);
        
        if (string.IsNullOrWhiteSpace(taskContent))
        {
            return new EmailCommandResult
            {
                Success = false,
                Message = "I couldn't extract the task details from your email. Please provide more specific information.",
                Action = EmailAction.QuickResponse,
                SuggestedResponses = { "Reply with 'Add Task: [your task description]'" }
            };
        }

        var priority = ExtractPriority(emailCommand.Body);
        var dueDate = ExtractDueDate(emailCommand.Body);

        try
        {
            var todoId = await _mediator.Send(new CreateTodoCommand(
                taskContent,
                Priority: priority,
                DueDate: dueDate,
                Description: emailCommand.Body));

            return new EmailCommandResult
            {
                Success = true,
                Message = $"Successfully created task: '{taskContent}'",
                Action = EmailAction.TodoCreated,
                Data = todoId,
                SuggestedResponses = { 
                    "Reply with 'Status' to see all your tasks",
                    "Reply with 'Add Task: [description]' to add another task"
                }
            };
        }
        catch (Exception)
        {
            return new EmailCommandResult
            {
                Success = false,
                Message = "Failed to create the task. Please try again.",
                Action = EmailAction.QuickResponse
            };
        }
    }

    private async Task<EmailCommandResult> HandleTodoFromPattern(EmailCommand emailCommand)
    {
        var pattern = _patternHandlers.Keys.First(p => p.IsMatch(emailCommand.Subject));
        var match = pattern.Match(emailCommand.Subject);
        
        if (match.Groups.Count > 3)
        {
            var taskContent = match.Groups[3].Value.Trim();
            var priority = ExtractPriority(emailCommand.Body);

            try
            {
                var todoId = await _mediator.Send(new CreateTodoCommand(
                    taskContent,
                    Priority: priority,
                    Description: emailCommand.Body));

                return new EmailCommandResult
                {
                    Success = true,
                    Message = $"Task created: '{taskContent}'",
                    Action = EmailAction.TodoCreated,
                    Data = todoId
                };
            }
            catch (Exception)
            {
                return new EmailCommandResult
                {
                    Success = false,
                    Message = "Failed to create task from email subject."
                };
            }
        }

        return await HandleTodoEmail(emailCommand);
    }

    private async Task<EmailCommandResult> HandleStatusRequest(EmailCommand emailCommand)
    {
        var todos = await _mediator.Send(new GetTodosQuery());
        
        var completed = todos.Count(t => t.Status == TodoStatus.Completed);
        var pending = todos.Count(t => t.Status == TodoStatus.NotStarted);
        var inProgress = todos.Count(t => t.Status == TodoStatus.InProgress);

        var statusMessage = $"Here's your current status:\n\n" +
                          $"📋 Total Tasks: {todos.Count()}\n" +
                          $"✅ Completed: {completed}\n" +
                          $"🔄 In Progress: {inProgress}\n" +
                          $"⭕ Not Started: {pending}\n\n";

        if (todos.Any())
        {
            statusMessage += "Recent tasks:\n";
            foreach (var todo in todos.OrderByDescending(t => t.CreatedAt).Take(5))
            {
                var status = todo.Status == TodoStatus.Completed ? "✅" : "⭕";
                statusMessage += $"{status} {todo.Title}\n";
            }
        }

        return new EmailCommandResult
        {
            Success = true,
            Message = statusMessage,
            Action = EmailAction.StatusReport,
            SuggestedResponses = { 
                "Reply with 'Add Task: [description]' to add a new task",
                "Reply with 'Help' to see available commands"
            }
        };
    }

    private async Task<EmailCommandResult> HandleReminderEmail(EmailCommand emailCommand)
    {
        // Extract reminder content and create a task with due date
        var pattern = new Regex(@"^reminder:?\s*(.+)", RegexOptions.IgnoreCase);
        var match = pattern.Match(emailCommand.Subject);
        
        if (match.Success)
        {
            var reminderContent = match.Groups[1].Value.Trim();
            var dueDate = ExtractDueDate(emailCommand.Body) ?? DateTime.Now.AddDays(1);

            try
            {
                var todoId = await _mediator.Send(new CreateTodoCommand(
                    $"Reminder: {reminderContent}",
                    Priority: Priority.Medium,
                    DueDate: dueDate,
                    Description: emailCommand.Body));

                return new EmailCommandResult
                {
                    Success = true,
                    Message = $"Reminder set for: '{reminderContent}' on {dueDate:MMM dd, yyyy}",
                    Action = EmailAction.TodoCreated,
                    Data = todoId
                };
            }
            catch (Exception)
            {
                return new EmailCommandResult
                {
                    Success = false,
                    Message = "Failed to set reminder."
                };
            }
        }

        return new EmailCommandResult
        {
            Success = false,
            Message = "Could not parse reminder details."
        };
    }

    private async Task<EmailCommandResult> HandleCompleteTaskEmail(EmailCommand emailCommand)
    {
        return new EmailCommandResult
        {
            Success = false,
            Message = "Task completion via email is coming soon!",
            Action = EmailAction.QuickResponse,
            SuggestedResponses = { "Use the CLI or desktop app to complete tasks for now" }
        };
    }

    private async Task<EmailCommandResult> HandleHelpRequest(EmailCommand emailCommand)
    {
        var helpMessage = @"Hey-Dav Email Commands:

📧 Subject Line Commands:
• 'Todo' or 'Task' - Creates a new task from email body
• 'Add Task: [description]' - Creates task with specific description
• 'Status' or 'Report' - Get your current task status
• 'Reminder: [description]' - Set a reminder task

📝 Email Body Keywords:
• Priority: high/medium/low - Sets task priority
• Due: YYYY-MM-DD - Sets due date
• Urgent - Sets high priority

💡 Examples:
• Subject: 'Add Task: Buy groceries' → Creates a new task
• Subject: 'Status' → Gets your task summary
• Subject: 'Reminder: Doctor appointment' with body 'Due: 2024-08-15' → Sets reminder

Reply to this email with any of these commands to get started!";

        return new EmailCommandResult
        {
            Success = true,
            Message = helpMessage,
            Action = EmailAction.QuickResponse,
            SuggestedResponses = { 
                "Try 'Add Task: Your task description'",
                "Try 'Status' to see your tasks"
            }
        };
    }

    private async Task<EmailCommandResult> ParseEmailBody(EmailCommand emailCommand)
    {
        // Try to extract actionable items from email body
        var body = emailCommand.Body.ToLowerInvariant();
        
        if (body.Contains("add task") || body.Contains("create task") || body.Contains("new task"))
        {
            return await HandleTodoEmail(emailCommand);
        }
        
        if (body.Contains("status") || body.Contains("report") || body.Contains("summary"))
        {
            return await HandleStatusRequest(emailCommand);
        }

        // Default response for unrecognized emails
        return new EmailCommandResult
        {
            Success = false,
            Message = "I couldn't understand your request. Please use specific command formats.",
            Action = EmailAction.QuickResponse,
            SuggestedResponses = { 
                "Reply with 'Help' to see available commands",
                "Use subject line 'Add Task: [description]' to create a task"
            }
        };
    }

    private static string ExtractTaskContent(EmailCommand emailCommand)
    {
        // Try to extract from subject first
        var subject = emailCommand.Subject.Trim();
        if (subject.ToLowerInvariant().StartsWith("add task:") || 
            subject.ToLowerInvariant().StartsWith("new task:") ||
            subject.ToLowerInvariant().StartsWith("todo:"))
        {
            var colonIndex = subject.IndexOf(':');
            if (colonIndex > 0 && colonIndex < subject.Length - 1)
            {
                return subject.Substring(colonIndex + 1).Trim();
            }
        }

        // Extract from body
        var lines = emailCommand.Body.Split('\n', StringSplitOptions.RemoveEmptyEntries);
        var firstLine = lines.FirstOrDefault()?.Trim();
        
        return !string.IsNullOrWhiteSpace(firstLine) ? firstLine : emailCommand.Subject;
    }

    private static Priority ExtractPriority(string text)
    {
        var lowerText = text.ToLowerInvariant();
        
        if (lowerText.Contains("high priority") || lowerText.Contains("urgent") || lowerText.Contains("important"))
            return Priority.High;
        if (lowerText.Contains("low priority") || lowerText.Contains("when you have time"))
            return Priority.Low;
        
        return Priority.Medium;
    }

    private static DateTime? ExtractDueDate(string text)
    {
        // Simple date extraction patterns
        var patterns = new[]
        {
            @"due:?\s*(\d{4}-\d{2}-\d{2})",
            @"by:?\s*(\d{4}-\d{2}-\d{2})",
            @"deadline:?\s*(\d{4}-\d{2}-\d{2})"
        };

        foreach (var pattern in patterns)
        {
            var match = Regex.Match(text, pattern, RegexOptions.IgnoreCase);
            if (match.Success && DateTime.TryParse(match.Groups[1].Value, out var date))
            {
                return date;
            }
        }

        return null;
    }

    private static EmailAction MapToEmailAction(CommandResult result)
    {
        if (!result.Success)
            return EmailAction.QuickResponse;

        return result.ProcessorUsed switch
        {
            "todo" when result.Message.Contains("created") => EmailAction.TodoCreated,
            "todo" when result.Message.Contains("completed") => EmailAction.TaskCompleted,
            "todo" when result.Message.Contains("status") => EmailAction.StatusReport,
            "goal" => EmailAction.GoalCreated,
            "schedule" => EmailAction.ScheduleUpdate,
            _ => EmailAction.QuickResponse
        };
    }

    private static string GetActionDescription(EmailAction action) => action switch
    {
        EmailAction.TodoCreated => "✅ Your task has been added to your todo list.",
        EmailAction.GoalCreated => "🎯 Your goal has been created.",
        EmailAction.StatusReport => "📊 Here's your current status summary.",
        EmailAction.TaskCompleted => "✅ Task marked as completed.",
        EmailAction.ScheduleUpdate => "📅 Your schedule has been updated.",
        EmailAction.QuickResponse => "💬 Quick response sent.",
        _ => ""
    };
}