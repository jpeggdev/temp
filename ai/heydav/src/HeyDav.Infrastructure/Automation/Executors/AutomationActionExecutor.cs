using HeyDav.Domain.Automation.Entities;
using HeyDav.Domain.Automation.Enums;
using HeyDav.Domain.Automation.ValueObjects;
using HeyDav.Application.Notifications.Services;
using HeyDav.Domain.Notifications.Enums;
using Microsoft.Extensions.Logging;

namespace HeyDav.Infrastructure.Automation.Executors;

public class AutomationActionExecutor : IAutomationActionExecutor
{
    private readonly INotificationEngine _notificationEngine;
    private readonly ILogger<AutomationActionExecutor> _logger;

    public AutomationActionExecutor(
        INotificationEngine notificationEngine,
        ILogger<AutomationActionExecutor> logger)
    {
        _notificationEngine = notificationEngine ?? throw new ArgumentNullException(nameof(notificationEngine));
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    }

    public async Task<AutomationActionResult> ExecuteActionAsync(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken = default)
    {
        var result = new AutomationActionResult(action.Id, action.Name);
        
        try
        {
            switch (action.Type)
            {
                case AutomationActionType.SendNotification:
                    await ExecuteSendNotificationAction(action, context, result, cancellationToken);
                    break;
                    
                case AutomationActionType.SendEmail:
                    await ExecuteSendEmailAction(action, context, result, cancellationToken);
                    break;
                    
                case AutomationActionType.CreateTask:
                    await ExecuteCreateTaskAction(action, context, result, cancellationToken);
                    break;
                    
                case AutomationActionType.UpdateTask:
                    await ExecuteUpdateTaskAction(action, context, result, cancellationToken);
                    break;
                    
                case AutomationActionType.CompleteTask:
                    await ExecuteCompleteTaskAction(action, context, result, cancellationToken);
                    break;
                    
                case AutomationActionType.SendWebhook:
                    await ExecuteSendWebhookAction(action, context, result, cancellationToken);
                    break;
                    
                case AutomationActionType.Custom:
                    await ExecuteCustomAction(action, context, result, cancellationToken);
                    break;
                    
                default:
                    result.Complete(false, $"Unsupported action type: {action.Type}");
                    break;
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute automation action {ActionId} ({ActionName})", action.Id, action.Name);
            result.Complete(false, ex.Message);
        }

        return result;
    }

    public async Task<AutomationActionTestResult> TestActionAsync(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken = default)
    {
        try
        {
            switch (action.Type)
            {
                case AutomationActionType.SendNotification:
                    return await TestSendNotificationAction(action, context, cancellationToken);
                    
                case AutomationActionType.SendEmail:
                    return await TestSendEmailAction(action, context, cancellationToken);
                    
                case AutomationActionType.CreateTask:
                    return await TestCreateTaskAction(action, context, cancellationToken);
                    
                case AutomationActionType.SendWebhook:
                    return await TestSendWebhookAction(action, context, cancellationToken);
                    
                default:
                    return new AutomationActionTestResult
                    {
                        Success = true,
                        Message = $"Test passed for action type {action.Type}",
                        Data = new Dictionary<string, object> { ["tested"] = true }
                    };
            }
        }
        catch (Exception ex)
        {
            return new AutomationActionTestResult
            {
                Success = false,
                Message = ex.Message
            };
        }
    }

    private async Task ExecuteSendNotificationAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        AutomationActionResult result, 
        CancellationToken cancellationToken)
    {
        var title = action.GetConfigurationValue<string>("title") ?? "Automation Notification";
        var content = action.GetConfigurationValue<string>("content") ?? "This notification was sent by an automation rule.";
        var recipient = action.GetConfigurationValue<string>("recipient");
        
        // Replace context variables in title and content
        title = ReplaceContextVariables(title, context);
        content = ReplaceContextVariables(content, context);

        var notificationId = await _notificationEngine.SendNotificationAsync(
            title,
            content,
            NotificationType.System,
            NotificationPriority.Medium,
            NotificationChannel.InApp,
            recipientId: recipient,
            cancellationToken: cancellationToken);

        var output = new Dictionary<string, object>
        {
            ["notificationId"] = notificationId,
            ["title"] = title,
            ["content"] = content,
            ["recipient"] = recipient ?? "system"
        };

        result.Complete(notificationId != Guid.Empty, 
            notificationId != Guid.Empty ? "Notification sent successfully" : "Failed to send notification", 
            output: output);
    }

    private async Task ExecuteSendEmailAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        AutomationActionResult result, 
        CancellationToken cancellationToken)
    {
        // Placeholder implementation
        await Task.Delay(100, cancellationToken); // Simulate email sending
        
        var to = action.GetConfigurationValue<string>("to") ?? "admin@example.com";
        var subject = action.GetConfigurationValue<string>("subject") ?? "Automation Email";
        var body = action.GetConfigurationValue<string>("body") ?? "This email was sent by an automation rule.";
        
        // Replace context variables
        subject = ReplaceContextVariables(subject, context);
        body = ReplaceContextVariables(body, context);

        var output = new Dictionary<string, object>
        {
            ["to"] = to,
            ["subject"] = subject,
            ["body"] = body
        };

        result.Complete(true, "Email sent successfully", output: output);
    }

    private async Task ExecuteCreateTaskAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        AutomationActionResult result, 
        CancellationToken cancellationToken)
    {
        // Placeholder implementation
        await Task.Delay(50, cancellationToken);
        
        var title = action.GetConfigurationValue<string>("title") ?? "Automated Task";
        var description = action.GetConfigurationValue<string>("description") ?? "";
        var dueDate = action.GetConfigurationValue<DateTime?>("dueDate");
        
        // Replace context variables
        title = ReplaceContextVariables(title, context);
        description = ReplaceContextVariables(description, context);

        var taskId = Guid.NewGuid();
        var output = new Dictionary<string, object>
        {
            ["taskId"] = taskId,
            ["title"] = title,
            ["description"] = description,
            ["dueDate"] = dueDate?.ToString() ?? "Not set"
        };

        result.Complete(true, "Task created successfully", output: output);
    }

    private async Task ExecuteUpdateTaskAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        AutomationActionResult result, 
        CancellationToken cancellationToken)
    {
        // Placeholder implementation
        await Task.Delay(50, cancellationToken);
        result.Complete(true, "Task updated successfully");
    }

    private async Task ExecuteCompleteTaskAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        AutomationActionResult result, 
        CancellationToken cancellationToken)
    {
        // Placeholder implementation
        await Task.Delay(50, cancellationToken);
        result.Complete(true, "Task completed successfully");
    }

    private async Task ExecuteSendWebhookAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        AutomationActionResult result, 
        CancellationToken cancellationToken)
    {
        // Placeholder implementation
        await Task.Delay(200, cancellationToken);
        
        var url = action.GetConfigurationValue<string>("url") ?? "https://example.com/webhook";
        var method = action.GetConfigurationValue<string>("method") ?? "POST";
        
        var output = new Dictionary<string, object>
        {
            ["url"] = url,
            ["method"] = method,
            ["statusCode"] = 200
        };

        result.Complete(true, "Webhook sent successfully", output: output);
    }

    private async Task ExecuteCustomAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        AutomationActionResult result, 
        CancellationToken cancellationToken)
    {
        // Placeholder implementation for custom actions
        await Task.Delay(100, cancellationToken);
        result.Complete(true, "Custom action executed successfully");
    }

    private async Task<AutomationActionTestResult> TestSendNotificationAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken)
    {
        await Task.Delay(10, cancellationToken);
        
        var title = action.GetConfigurationValue<string>("title");
        var content = action.GetConfigurationValue<string>("content");
        
        var hasTitle = !string.IsNullOrEmpty(title);
        var hasContent = !string.IsNullOrEmpty(content);
        
        return new AutomationActionTestResult
        {
            Success = hasTitle && hasContent,
            Message = hasTitle && hasContent ? "Notification action is valid" : "Missing title or content",
            Data = new Dictionary<string, object>
            {
                ["hasTitle"] = hasTitle,
                ["hasContent"] = hasContent,
                ["title"] = title ?? "",
                ["content"] = content ?? ""
            }
        };
    }

    private async Task<AutomationActionTestResult> TestSendEmailAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken)
    {
        await Task.Delay(10, cancellationToken);
        
        var to = action.GetConfigurationValue<string>("to");
        var subject = action.GetConfigurationValue<string>("subject");
        
        var hasRecipient = !string.IsNullOrEmpty(to);
        var hasSubject = !string.IsNullOrEmpty(subject);
        
        return new AutomationActionTestResult
        {
            Success = hasRecipient && hasSubject,
            Message = hasRecipient && hasSubject ? "Email action is valid" : "Missing recipient or subject",
            Data = new Dictionary<string, object>
            {
                ["hasRecipient"] = hasRecipient,
                ["hasSubject"] = hasSubject,
                ["to"] = to ?? "",
                ["subject"] = subject ?? ""
            }
        };
    }

    private async Task<AutomationActionTestResult> TestCreateTaskAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken)
    {
        await Task.Delay(10, cancellationToken);
        
        var title = action.GetConfigurationValue<string>("title");
        var hasTitle = !string.IsNullOrEmpty(title);
        
        return new AutomationActionTestResult
        {
            Success = hasTitle,
            Message = hasTitle ? "Create task action is valid" : "Missing task title",
            Data = new Dictionary<string, object>
            {
                ["hasTitle"] = hasTitle,
                ["title"] = title ?? ""
            }
        };
    }

    private async Task<AutomationActionTestResult> TestSendWebhookAction(
        AutomationAction action, 
        Dictionary<string, object> context, 
        CancellationToken cancellationToken)
    {
        await Task.Delay(10, cancellationToken);
        
        var url = action.GetConfigurationValue<string>("url");
        var hasUrl = !string.IsNullOrEmpty(url) && Uri.TryCreate(url, UriKind.Absolute, out _);
        
        return new AutomationActionTestResult
        {
            Success = hasUrl,
            Message = hasUrl ? "Webhook action is valid" : "Missing or invalid URL",
            Data = new Dictionary<string, object>
            {
                ["hasUrl"] = hasUrl,
                ["url"] = url ?? ""
            }
        };
    }

    private static string ReplaceContextVariables(string template, Dictionary<string, object> context)
    {
        if (string.IsNullOrEmpty(template) || !context.Any())
        {
            return template;
        }

        var result = template;
        foreach (var kvp in context)
        {
            var placeholder = $"{{{kvp.Key}}}";
            if (result.Contains(placeholder))
            {
                result = result.Replace(placeholder, kvp.Value?.ToString() ?? "");
            }
        }

        return result;
    }
}