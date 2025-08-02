using HeyDav.Application.Notifications.Services;
using HeyDav.Domain.Notifications.Entities;
using HeyDav.Infrastructure.Email;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;

namespace HeyDav.Application.Notifications.Channels;

public class EmailNotificationChannel : INotificationChannel
{
    private readonly IEmailService _emailService;
    private readonly EmailChannelOptions _options;
    private readonly ILogger<EmailNotificationChannel> _logger;

    public string Name => "Email";
    public bool IsEnabled => _options.Enabled;

    public EmailNotificationChannel(
        IEmailService emailService,
        IOptions<EmailChannelOptions> options,
        ILogger<EmailNotificationChannel> logger)
    {
        _emailService = emailService ?? throw new ArgumentNullException(nameof(emailService));
        _options = options?.Value ?? throw new ArgumentNullException(nameof(options));
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    }

    public async Task<NotificationDeliveryResult> SendAsync(Notification notification, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!IsEnabled)
            {
                return new NotificationDeliveryResult(false, "Email channel is disabled");
            }

            if (string.IsNullOrEmpty(notification.RecipientEmail))
            {
                return new NotificationDeliveryResult(false, "No recipient email address specified");
            }

            var subject = notification.Title;
            var body = FormatEmailBody(notification);

            await _emailService.SendEmailAsync(
                notification.RecipientEmail,
                subject,
                body,
                isHtml: true,
                cancellationToken: cancellationToken);

            _logger.LogDebug("Sent email notification {NotificationId} to {Email}",
                notification.Id, notification.RecipientEmail);

            return new NotificationDeliveryResult(true, metadata: new Dictionary<string, object>
            {
                ["channel"] = "Email",
                ["recipient"] = notification.RecipientEmail,
                ["deliveredAt"] = DateTime.UtcNow
            });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send email notification {NotificationId} to {Email}",
                notification.Id, notification.RecipientEmail);
            return new NotificationDeliveryResult(false, ex.Message);
        }
    }

    public async Task<bool> TestAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            if (!IsEnabled || string.IsNullOrEmpty(_options.TestRecipient))
            {
                return false;
            }

            await _emailService.SendEmailAsync(
                _options.TestRecipient,
                "HeyDav Notification System Test",
                "This is a test email from the HeyDav notification system. If you receive this, email notifications are working correctly.",
                cancellationToken: cancellationToken);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Email channel test failed");
            return false;
        }
    }

    public Task<Dictionary<string, object>> GetStatusAsync(CancellationToken cancellationToken = default)
    {
        return Task.FromResult(new Dictionary<string, object>
        {
            ["status"] = IsEnabled ? "enabled" : "disabled",
            ["enabled"] = IsEnabled,
            ["smtpServer"] = _options.SmtpServer ?? "not configured",
            ["lastCheck"] = DateTime.UtcNow
        });
    }

    public Task<bool> ConfigureAsync(Dictionary<string, object> configuration, CancellationToken cancellationToken = default)
    {
        // Email configuration is typically done through app settings
        // This could be extended to support runtime configuration
        return Task.FromResult(true);
    }

    private string FormatEmailBody(Notification notification)
    {
        var html = $@"
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>{notification.Title}</title>
    <style>
        body {{ font-family: Arial, sans-serif; line-height: 1.6; color: #333; }}
        .container {{ max-width: 600px; margin: 0 auto; padding: 20px; }}
        .header {{ background: #f4f4f4; padding: 20px; text-align: center; }}
        .content {{ padding: 20px; }}
        .footer {{ background: #f4f4f4; padding: 10px; text-align: center; font-size: 12px; color: #666; }}
        .priority-high {{ border-left: 4px solid #ff4444; }}
        .priority-medium {{ border-left: 4px solid #ffaa00; }}
        .priority-low {{ border-left: 4px solid #00aa00; }}
        .actions {{ margin-top: 20px; }}
        .action-button {{ 
            display: inline-block; 
            padding: 10px 20px; 
            background: #007cba; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin-right: 10px; 
        }}
    </style>
</head>
<body>
    <div class='container priority-{notification.Priority.ToString().ToLower()}'>
        <div class='header'>
            <h1>{notification.Title}</h1>
            <p><strong>Priority:</strong> {notification.Priority}</p>
        </div>
        <div class='content'>
            <p>{notification.Content.Replace("\n", "<br>")}</p>";

        if (notification.Metadata.ImageUrl != null)
        {
            html += $"<p><img src='{notification.Metadata.ImageUrl}' alt='Notification Image' style='max-width: 100%; height: auto;'></p>";
        }

        if (notification.Actions.Actions.Any())
        {
            html += "<div class='actions'>";
            foreach (var action in notification.Actions.Actions)
            {
                // Note: In a real implementation, these would be secure links with tokens
                html += $"<a href='#' class='action-button'>{action.Title}</a>";
            }
            html += "</div>";
        }

        html += $@"
        </div>
        <div class='footer'>
            <p>This notification was sent by HeyDav at {notification.CreatedAt:yyyy-MM-dd HH:mm:ss} UTC</p>
            <p>Notification ID: {notification.Id}</p>
        </div>
    </div>
</body>
</html>";

        return html;
    }
}

public class EmailChannelOptions
{
    public bool Enabled { get; set; } = false;
    public string? SmtpServer { get; set; }
    public int SmtpPort { get; set; } = 587;
    public string? SmtpUsername { get; set; }
    public string? SmtpPassword { get; set; }
    public bool UseSSL { get; set; } = true;
    public string? FromAddress { get; set; }
    public string? FromName { get; set; }
    public string? TestRecipient { get; set; }
}